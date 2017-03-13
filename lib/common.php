<?php

define('NUM_COUNTIES', 42);

define('STAGES', [
  'gim' => range(5, 8),
  'lic' => range(9, 12),
]);

define('COUNTY_NAMES', [
  null, 'Alba', 'Arad', 'Argeș', 'Bacău', 'Bihor', 'Bistrița-Năsăud', 'Botoșani', 'Brăila', 'Brașov',
  'București', 'Buzău', 'Călărași', 'Caraș-Severin', 'Cluj', 'Constanța', 'Covasna', 'Dâmbovița', 'Dolj', 'Galați',
  'Giurgiu', 'Gorj', 'Harghita', 'Hunedoara', 'Ialomița', 'Iași', 'Ilfov', 'Maramureș', 'Mehedinți', 'Mureș',
  'Neamț', 'Olt', 'Prahova', 'Sălaj', 'Satu-Mare', 'Sibiu', 'Suceava', 'Teleorman', 'Timiș', 'Tulcea',
  'Vâlcea', 'Vaslui', 'Vrancea',
]);
define('COUNTY_CODES', [
  null, 'AB', 'AA', 'AG', 'BC', 'BH', 'BN', 'BT', 'BR', 'BV',
  'Buc', 'BZ', 'CL', 'CS', 'CJ', 'CT', 'CV', 'DB', 'DJ', 'GL',
  'GR', 'GJ', 'HR', 'HD', 'IL', 'IS', 'IF', 'MM', 'MH', 'MS',
  'NT', 'OT', 'PH', 'SJ', 'SM', 'SB', 'SV', 'TR', 'TM', 'TL',
  'VL', 'VS', 'VN',
]);

define('URL_INDEX', 'http://olimpiada.info/oji%s/index.php?cid=rezultate&w=%s');
define('URL_SCORES', 'http://olimpiada.info/oji%s/index.php?cid=rezultate&w=%s&judet=%s&clasa=%s');
define('RAW_FILE_PATTERN', 'raw/%d-%d-%02d.html');
define('HTML_FILE_PATTERN', 'www/%d-%d.html');
define('CSV_FILE_PATTERN', 'www/csv/%d-%d.csv');

mb_internal_encoding("UTF-8");
