# oji-score-parser

Aceste scripturi descarcă și indexează paginile referitoare la OJI de pe site-ul [olimpiada.info](http://olimpiada.info/). Pe acest site apar doar clasamente județene. Scopul scripturilor este să interclaseze clasamentele județene pentru a publica un clasament național.

Sistemul este făcut un pic pe genunchi, din lipsă de timp. Fișierele HTML de la olimpiada.info au multe incorectitudini. Ca să le putem totuși parsa fără intervenție manuală, folosim [Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/), o bibliotecă destul de învechită, dar care se descurcă cu parsarea.

Sistemul are două componente:

## downloader.php

Acest program:

1. Descarcă paginile-index cu toate județele, pentru gimnaziu și pentru liceu (de exemplu [liceu 2015](http://olimpiada.info/oji2015/index.php?cid=rezultate&w=lic)).
2. Urmează link-urile din tabel către clasamentele județene și le descarcă.

Programul salvează paginile HTML în fișierul `raw/<an>-<clasă>-<județ>.html`. De exemplu, [2015, București clasa a 11-a](http://olimpiada.info/oji2015/index.php?cid=rezultate&w=lic&judet=10&clasa=11) este salvat în `raw/2015-11-10.html` (codul Bucureștiului la olimpiada.info se întâmplă să fie 10).

Paginile deja existente în directorul `raw` nu mai sunt redescărcate în mod normal.

Opțiuni:

* În `downloader.php` puteți modifica constanta `DOWNLOAD_YEARS` pentru a descărca alți ani.
* Puteți pasa argumentul `--force` în linia de comandă pentru a forța descărcarea tuturor clasamentelor județene, inclusiv a celor deja existente în `raw/`. Acest argument poate fi util, căci paginile sunt actualizate ocazional cu informații noi (elevi retrași etc.).

## oji-score-parser.php

Acest program:

1. Parsează fișierele descărcate în directorul `raw/`, pentru anii specificați.
2. Generează pagini HTML cu clasamente naționale prin interclasarea clasamentelor județene.
3. Generează fișiere CSV corespunzătoare.

Programul salvează paginile HTML în directorul `www/`, care în final este publicabil undeva pe Internet. Pentru anii trecuți, clasamentele naționale sunt disponibile la [algopedia.ro/oji2017](http://algopedia.ro/oji2017/). Directorul `www/` include deja fișierele JavaScript și CSS necesare.

Opțiuni:

* Constanta `PROCESS_YEARS` specifică anii pentru care doriți regenerarea fișierelor HTML (pentru toate clasele 5-12).
* Constanta `LINK_YEARS` specifică anii pe care doriți să-i conțină _drop down_-ul din antet (legături către alte clasamente generate din anii anteriori).
* Fișierul `www/.htaccess` specifică fișierul HTML care doriți să fie prezentat ca index.
