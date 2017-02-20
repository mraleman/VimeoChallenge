<?php namespace Vimeochallenge\Source;

//This should be the root directory of where your API lives.
const API_ROOT = 'http://localhost';
//Hostname for our Database.
const DB_HOST = 'localhost';
//Username for our Database.
const DB_USER = '';
//Password for our Database.
const DB_PASS = '';
//Name for the Database. It will be created when running DUMPER.
const DB_NAME = 'vimeochallenge';
//Database port, default 3306.
const DB_PORT = '3306';
//Location of your DUMPFILE.
const DUMPFILE = __DIR__.'/dumper/data.dump';
//Linebreak used for the dump output.
const LINEBREAK = PHP_EOL.'------------------------------------------'.PHP_EOL;
//Limit how many lines to process, leave as 0 to process all.
const DUMP_READ = 0;
//Set how many records should be inserted in the SQL INSERT.
const DUMP_CHUNK = 1000;
