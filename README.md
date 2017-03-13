# oji-score-parser

Aceste scripturi descarcă și indexează paginile referitoare la OJI de pe site-ul [olimpiada.info](http://olimpiada.info/). Pe acest site apar doar clasamente județene. Scopul scripturilor este să interclaseze clasamentele județene pentru a publica un clasament național.

Sistemul are două componente:

## downloader.php

Acest program:

1. Descarcă paginile-index cu toate județele, pentru gimnaziu și pentru liceu (de exemplu [liceu 2015](http://olimpiada.info/oji2015/index.php?cid=rezultate&w=lic)).
2. Urmează link-urile din tabel către clasamentele județene și le descarcă.

Programul salvează paginile HTML în fișierul `raw/<an>-<clasă>-<județ>.html`. De exemplu, [2015, București clasa a 11-a](http://olimpiada.info/oji2015/index.php?cid=rezultate&w=lic&judet=10&clasa=11) este salvat în `raw/2015-11-10.html` (codul Bucureștiului la olimpiada.info se întâmplă să fie 10).

Paginile deja existente în directorul `raw` nu mai sunt redescărcate.

Opțiuni:

* În `downloader.php` puteți modifica constanta `DOWNLOAD_YEARS` pentru a descărca alți ani.
* Puteți pasa argumentul `--force` în linia de comandă pentru a forța descărcarea tuturor clasamentelor județene, inclusiv a celor deja existente în `raw/`. Acest argument poate fi util, căci paginile sunt actualizate ocazional cu informații noi (elevi retrași etc.).
