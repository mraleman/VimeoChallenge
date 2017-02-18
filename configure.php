<?php namespace Vimeochallenge\Source;

const API_ROOT = 'http://localhost';//this should be the root directory of where your API lives
const DB_HOST = 'localhost';//hostname for db
const DB_USER = '';//database user name
const DB_PASS = '';//database password
const DB_NAME = 'vimeochallenge';//name for database, you can rename if desired
const DB_PORT = '3306';//database port, default 3306
const DUMP_READ = 0;//limit how many lines to process, leave as 0 to process all
const DUMP_CHUNK = 5000;//set how many records should be inserted in the SQL INSERT