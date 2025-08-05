<?php

use Aatis\HttpFoundation\Component\Request;
use App\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

$time_start = microtime(true);

session_start();

(new Kernel())->handle(Request::createFromGlobals());

$time_end = microtime(true);

$execution_time = ($time_end - $time_start);

echo '<br>Execution time: '.number_format($execution_time, 2).' seconds';
