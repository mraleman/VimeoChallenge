<?php
ini_set('display_errors',1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
//define JSON Header
//header('Content-Type: application/json');

use Vimeochallenge\Source as API;

require 'app/init.php';

/**
 * Initiate app by calling Bootstrap class
 */

$app = new API\Bootstrap();