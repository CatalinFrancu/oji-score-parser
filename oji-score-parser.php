#!/usr/bin/php
<?php

require 'lib/common.php';
require 'lib/third-party/simple_html_dom.php';
require 'lib/third-party/smarty/Smarty.class.php';

// years to reprocess
define('PROCESS_YEARS', range(2013, 2017));

// years to hyperlink in the resulting pages
define('LINK_YEARS', range(2013, 2017));

$THRESHOLD = [5 => 20, 6 => 20, 7 => 20, 8 => 20, 9 => 30, 10 => 30, 11 => 30, 12 => 30];

define('Q_PASS', 0);
define('Q_ASSUMED_PASS', 1);
define('Q_FAIL', 2);
define('Q_ASSUMED_FAIL', 3);
define('Q_RETIRED', 4);
define('Q_UNDER_THRESHOLD', 5);
define('Q_ABSENT', 6);
define('Q_UNKNOWN', 7);
$PASS_NAMES = [
  'calificat',
  'calificat?',
  'picat',
  'picat?',
  'retras',
  'sub barem',
  'absent',
  'necunoscut',
];
$PASS_LONG_NAMES = [
  'elev calificat',
  'elev presupus calificat (nu au fost publicate datele)',
  'elev picat',
  'elev presupus picat (nu au fost publicate datele)',
  'elev retras',
  'sub baremul minim',
  'elev absent',
  'situație necunoscută'
];

// $SEATS[$year][$county][ 0 => seats_9_12, 1 => seats_5_8]
$SEATS = [
  2016 => [
    null, [4, 4], [4, 4], [12, 13], [9, 5], [8, 4], [11, 6], [5, 4], [7, 8], [11, 5],
    [33, 57], [5, 4], [4, 4], [4, 4], [12, 7], [6, 8], [4, 4], [8, 4], [13, 14], [10, 6],
    [4, 4], [10, 5], [4, 4], [5, 4], [4, 4], [13, 13], [4, 4], [6, 6], [4, 4], [11, 5],
    [6, 5], [9, 6], [12, 15], [5, 4], [4, 4], [4, 4], [10, 5], [4, 4], [10, 4], [4, 4],
    [12, 9], [9, 4], [7, 4],
  ],
];

// TODO: Parse table headers and look for P1, P2, [P3]. Currently not working due to broken HTML.
$NUM_PROBLEMS = [
  2013 => [ 5 => 2, 6 => 2, 7 => 2, 8 => 2, 9 => 2, 10 => 2, 11 => 2, 12 => 2, ],
  2014 => [ 5 => 2, 6 => 2, 7 => 2, 8 => 2, 9 => 2, 10 => 2, 11 => 2, 12 => 2, ],
  2015 => [ 5 => 2, 6 => 2, 7 => 2, 8 => 2, 9 => 2, 10 => 2, 11 => 2, 12 => 2, ],
  2016 => [ 5 => 2, 6 => 2, 7 => 2, 8 => 2, 9 => 2, 10 => 2, 11 => 2, 12 => 2, ],
  2017 => [ 5 => 2, 6 => 2, 7 => 2, 8 => 2, 9 => 3, 10 => 3, 11 => 3, 12 => 3, ],
];

foreach (PROCESS_YEARS as $year) {
  foreach (range(5, 12) as $grade) {
    print "Procesez anul {$year}, clasa a {$grade}-a\n";
    $outputFile = sprintf(HTML_FILE_PATTERN, $year, $grade);
    $csvFile = sprintf(CSV_FILE_PATTERN, $year, $grade);
    $sc = new ScoreComparer($year, $grade);
    $yearData = [];
    $numProblems = $NUM_PROBLEMS[$year][$grade];

    foreach (range(1, NUM_COUNTIES) as $county) {
      $rawFile = sprintf(RAW_FILE_PATTERN, $year, $grade, $county);

      if (file_exists($rawFile)) {
        $data = [];
        $contents = file_get_contents($rawFile);
        $html = str_get_html($contents);
        $rows = $html->find('table[bgcolor=#999999] tr');

        if ($numProblems == 2) {
          $statusCol = 8;
          $totalCol = 7;
          $scoreCols = [ 5, 6 ];
        } else if ($numProblems == 3) {
          $statusCol = 9;
          $totalCol = 8;
          $scoreCols = [ 5, 6, 7 ];
        } else {
          die("Number of problems ({$numProblems}) not supported.\n");
        }

        // Make a first pass and collect data
        $lastScore = null;
        foreach ($rows as $i => $row) {
          if ($i) {
            $cells = $row->find('td');
            $status = fixStatus($cells[$statusCol]->plaintext);
            $total = (int)fixString($cells[$totalCol]->plaintext);
            if ($status == 'calificat') {
              $pass = Q_PASS;
              $lastScore = $total;
            } else if ($status == 'absent') {
              $pass = Q_ABSENT;
            } else if ($status == '') {
              $pass = Q_UNKNOWN;
            } else {
              die("stare necunoscută: [{$status}]\n");
            }

            $scores = [];
            foreach ($scoreCols as $col) {
              $scores[] = (int)fixString($cells[$col]->plaintext);
            }

            $data[] = [
              'county' => $county,
              'name' => fixString($cells[1]->plaintext) . ' ' . fixString($cells[2]->plaintext),
              'school' => fixString($cells[3]->plaintext),
              'scores' => $scores,
              'total' => $total,
              'pass' => $pass,
            ];
          }
        }

        if ($lastScore !== null) {
          // Qualified students are named explicitly
          foreach ($data as $i => &$r) {
            if ($r['pass'] == Q_UNKNOWN) {
              $r['pass'] = ($r['total'] > $lastScore) ? Q_RETIRED : Q_FAIL;
            }
          }
        } else if (isset($SEATS[$year][$county])) {
          // Qualified students not named, but we know the number of seats.
          $seats = (int) ($SEATS[$year][$county][($grade <= 8) ? 1 : 0] / 4);
          foreach ($data as $i => &$r) {
            $r['pass'] = ($i < $seats) ? Q_ASSUMED_PASS : Q_ASSUMED_FAIL;
          }
        }

        foreach ($data as $i => &$r) {
          if (($r['total'] < $THRESHOLD[$grade]) &&
              ($r['pass'] != Q_ABSENT)) {
            $r['pass'] = Q_UNDER_THRESHOLD;
          }
        }

        $yearData = array_merge($yearData, $data);
      }
    }

    usort($yearData, [$sc, 'cmp']);
    writeCsv($yearData, $csvFile);

    $smarty = new Smarty();
    $smarty->template_dir = '.';
    $smarty->compile_dir = '/tmp';
    $smarty->assign('data', $yearData);
    $smarty->assign('years', array_reverse(LINK_YEARS));
    $smarty->assign('countyCodes', COUNTY_CODES);
    $smarty->assign('countyNames', COUNTY_NAMES);
    $smarty->assign('passNames', $PASS_NAMES);
    $smarty->assign('passLongNames', $PASS_LONG_NAMES);
    $smarty->assign('grade', $grade);
    $smarty->assign('year', $year);
    $smarty->assign('numProblems', $numProblems);
    $output = $smarty->fetch('layout.tpl');
    file_put_contents($outputFile, $output);
  }
}

/**************************************************************************/

function fixString($s) {
  $s = trim($s);
  $s = str_replace(['&#258;', '&#259;', '&#350;', '&#351;', '&#354;', '&#355;',
                    '&#536;', '&#537;', '&#538;', '&#539;', 'Ã', 'ã',
                    'ª', 'Þ', '\&quot;', '&quot;', '„', '”', '“', "'"],
                   ['Ă', 'ă', 'Ș', 'ș', 'Ț', 'ț',
                    'Ș', 'ș', 'Ț', 'ț', 'Ă', 'ă',
                    'Ș', 'Ț', '', '', '', '', '', ''],
                   $s);
  $s = mb_convert_case($s, MB_CASE_TITLE);

  // strip some school names
  $s = str_ireplace(
    ['liceul teoretic international de informatică bucuresti',
     'liceul teoretic internațional de informatică bucurești',
     'liceul teoretic interna?ional de informatică bucure?ti',
    ], 'ICHB', $s);
  $s = str_ireplace('liceul teoretic internațional de informatică constanța',
                    'ICHC', $s);
  $s = str_ireplace('palatul copiilor', 'PaCo', $s);
  $s = str_ireplace([
    'colegiul economic ',
    'colegiul național de informatică ',
    'colegiul national de informatica ',
    'colegiul national de informatică ',
    'colegiul na?ional de informatică ',
    'colegiul national bilingv ',
    'colegiul na?ional bilingv ',
    'colegiul national militar ',
    'colegiul national ',
    'colegiul național ',
    'colegiul na?ional ',
    'colegiul tehnic de electronica si telecomunica?ii ', 
    'colegiul tehnic ',
    'colegiul tehnic',
    'colegiul ',
    'col.nat. ',
    'comuna ',
    'liceul de informatica ',
    'liceul tehnologic ',
    'liceul teoretic de informatica ',
    'liceul teoretic ',
    'liceul ',
    'municipiul ',
    'orașul ',
    'scoala gimnaziala ',
    'Școala gimnazială ',
    'școala gimnazială ',
    'scoala ',
  ], '', $s);

  return $s;
}

function fixStatus($s) {
  $s = trim($s);
  $s = strtolower($s);
  return $s;
}

class ScoreComparer {
  var $year;
  var $grade;

  function ScoreComparer($year, $grade) {
    $this->year = $year;
    $this->grade = $grade;
  }

  function cmp($a, $b) {
    // higher total score
    if ($a['total'] != $b['total']) {
      return ($a['total'] < $b['total']) ? 1 : -1;
    }

    // more problems solved perfectly
    $perfectA = 0;
    foreach ($a['scores'] as $score) {
      $perfectA += ($score == 100);
    }
    $perfectB = 0;
    foreach ($b['scores'] as $score) {
      $perfectB += ($score == 100);
    }
    if ($perfectA != $perfectB) {
      return ($perfectA < $perfectB) ? 1 : -1;
    }

    // pass status
    if ($a['pass'] != $b['pass']) {
      return ($a['pass'] < $b['pass']) ? -1 : 1;
    }

    // county
    $s = strcmp($a['county'], $b['county']);
    if ($s != 0) {
      return $s;
    }

    // name
    return strcmp($a['name'], $b['name']);
  }
}

// Clobbers the data
function writeCsv($data, $file) {
  global $PASS_NAMES;

  $f = fopen($file, 'w');
  foreach($data as $row) {
    $row['county'] = COUNTY_NAMES[$row['county']];
    $row['pass'] = $PASS_NAMES[$row['pass']];
    $row['scores'] = implode(';', $row['scores']); // convert scores from array to string
    fputcsv($f, $row);
  }
  fclose($f);
}

?>
