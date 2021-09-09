<?php

namespace Globus;

define("ABS_PATH", dirname(__FILE__));

include(ABS_PATH . "/LoadManager/LoadManager.php");

use Globus\GlobusConfig as Config;
use Globus\LoadManager\LoadManager;

$debug = $argv[1] == '--debug';
$manager = new LoadManager($debug);

$manager->load();