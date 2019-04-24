<?php

require realpath('../vendor/autoload.php');

use SouthCoast\Console\Console;
use SouthCoast\Console\Cron;
use SouthCoast\Console\ErrorHandler;

/* To show only relevant information we strip the application root path from the stack trace */
ErrorHandler::setApplicationRoot('/Users/cornedejong/Development/composer_packages/southcoast-console');
/* register the handler */
ErrorHandler::register();

$value = Cron::register('root', 'php', __DIR__ . '/TestCron.php', ['somevalue' => 'this', 'oops'], ['*', '*', '*', '*', 1]);

Console::log($value);
