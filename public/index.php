<?php

use Aatis\HttpFoundation\Component\Request;
use App\Kernel;

require dirname(__DIR__).'/vendor/autoload.php';

session_start();

(new Kernel())->handle(Request::createFromGlobals());
