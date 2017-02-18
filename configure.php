<?php

define('API_ROOT','http://localhost/api');//this should be the root directory of where your API lives
define('DB_HOST','localhost');//hostname for db
define('DB_USER','');//database user name
define('DB_PASS','');//database password
define('DB_NAME', 'vimeochallenge');//name for database, you can rename if desired
define('DB_PORT','3306');//database port, default 3306
define('DUMP_READ', 0);//limit how many lines to process, leave as 0 to process all
define('DUMP_CHUNK', 5000);//set how many records should be inserted in the SQL INSERT