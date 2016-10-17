<?php

use Symfony\Component\Console\Input\InputOption;

set_time_limit(0);

$app = require_once __DIR__ . '/../app.php';

/** @var Knp\Console\Application $cli */
$cli = $app['console'];
$cli->run();