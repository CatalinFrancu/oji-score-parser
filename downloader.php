#!/usr/bin/php
<?php

require 'lib/common.php';
require 'lib/third-party/simple_html_dom.php';

// years to download
define('DOWNLOAD_YEARS', range(2013, 2017));

$force = false;
foreach ($argv as $i => $arg) {
  if ($i) {
    switch ($arg) {
      case '--force': $force = true; break;
      default: print "Unknown flag $arg; aborting\n"; exit;
    }
  }
}

foreach (DOWNLOAD_YEARS as $year) {
  foreach (STAGES as $stage => $grades) {
    printf("*** Year $year, stage $stage\n");
    sleep(2);

    $url = sprintf(URL_INDEX, $year, $stage);
    // download the file locally first
    $contents = file_get_contents($url);
    $html = str_get_html($contents);
    $rows = $html->find('table[bgcolor=#EEEEEE] tr');

    foreach ($rows as $county => $row) {
      if ($county) { // skip header row
        $cells = $row->find('td');
        foreach ($grades as $j => $grade) {
          $rawFile = sprintf(RAW_FILE_PATTERN, $year, $grade, $county);
          $text = trim($cells[$j + 2]->plaintext);
          $hasResults = ($text != '-');
          $hasRaw = file_exists($rawFile);

          if ($hasResults && (!$hasRaw || $force)) {
            sleep(2);
            $scoreUrl = sprintf(URL_SCORES, $year, $stage, $county, $grade);
            printf("Fetching [%s] to file [%s]\n", $scoreUrl, $rawFile);
            file_put_contents($rawFile, file_get_contents($scoreUrl));
          }
        }
      }
    }
  }
}
