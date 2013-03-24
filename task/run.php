#!/usr/bin/php
<?php
/*ini_set('display_errors', 1);
error_reporting(-1);*/

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'Gesdon'.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'App.php';

\Gesdon\Core\App::runTask()->run();