#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';

use App\QueueListenerCommand;

use Symfony\Component\Console\Application;

$application = new Application("ActiveMQ Stomp Demo", "1.0");
$application->add(new QueueListenerCommand);
$application->run();