<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css?v=3">
    <title>Clasament pe țară OJI {$year} clasa a {$grade}-a</title>
  </head>
  <body>
    <header>
      <div>
        <span class="logo">Clasamente pe țară la OJI</span>
        <form>
          <select>
            {foreach $years as $y}
              {for $g = 5 to 12}
                <option value="{$y}-{$g}.html" {if $y == $year && $g == $grade}selected{/if}>{$y} clasa a {$g}-a</option>
              {/for}
            {/foreach}
          </select>
        </form>
        <a id="csvLink" class="menu" href="csv/{$year}-{$grade}.csv">descarcă CSV</a>
      </div>
    </header>

    <div class="hint">
      <span>Clic pe orice rând pentru a vedea statistici până la acel rând inclusiv.</span>
    </div>

    <table id="rankings">
      <thead>
        <tr>
          <th>loc</th>
          <th>județ</th>
          <th>nume</th>
          <th>școală</th>
          <th>scor</th>
          <th>rezultat</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$data key=i item=r}
          <tr data-county="{$countyNames[$r.county]}">
            <td>{$i+1}</td>
            <td class="help" title="{$countyNames[$r.county]}">
              {$countyCodes[$r.county]}
            </td>
            <td class="studentName">{$r.name}</td>
            <td>
              <div class="schoolName help" title="{$r.school}">
                {$r.school}
              </div>
            </td>

            {capture "title"}
            {if $numProblems == 2}
              {$r.scores[0]} + {$r.scores[1]} = {$r.total}
            {else}
              {$r.scores[0]} + {$r.scores[1]}  + {$r.scores[2]} = {$r.total}
            {/if}
            {/capture}

            <td class="numeric help" title="{$smarty.capture.title}">
              {$r.total}
            </td>
            <td class="pass{$r.pass} help" title="{$passLongNames[$r.pass]}">
              {$passNames[$r.pass]}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>

    <div id="stats">
      Pentru primele <span id="sampleSize"></span> de locuri distribuția este:
      <a id="statsHide" href="#">închide</a>

      <table id="distribution">
        <thead>
          <tr>
            <th>județ</th>
            <th>elevi</th>
            <th>procentaj</th>
          </tr>
        </thead>
        <tbody>
          <tr class="stem">
            <td class="county"></td>
            <td class="frequency numeric"></td>
            <td class="percentageCell"><span class="percentage"></span>%</td>
          </tr>
        </tbody>
      </table>
    </div>

    <footer>
      {strip}
      <div class="licenseImg">
        <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">
          <img alt="Creative Commons License" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png"/>
        </a>
      </div>
      {/strip}

      <p>
        Copyright Cătălin Frâncu 2016<br/>

        Această pagină se bazează pe date preluate de la
        <a href="http://olimpiada.info/">olimpiada.info</a>.<br/>

        Această pagină poartă licența
        <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">
          Creative Commons Attribution-ShareAlike 4.0 International License</a>.
      </p>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="stats.js"></script>
  </body>
</html>
