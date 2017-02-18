<?php

//define JSON Header
header('Content-Type: application/json');

/**
 * Ideally we would like to use an autoloader along with namespaces
 * This will do for now.
 */
require '../configure.php';
require_once '../source/lib/Bootstrap.php';

/**
 * Initiate app by calling Bootstrap class
 */
$app = new Bootstrap;