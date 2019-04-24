<?php

require realpath('../vendor/autoload.php');

use SouthCoast\Console\ErrorHandler;

/* To show only relevant information we strip the application root path from the stack trace */

/* the error handler looks either at the 'app_root' constant */
define('app_root', '/Users/cornedejong/Development/composer_packages/southcoast-console');
/* Or the path defined by this method, the path defined by this method will be leading! */
ErrorHandler::setApplicationRoot('/Users/cornedejong/Development/composer_packages/southcoast-console');
/* register the handler */
ErrorHandler::register();

/* and you're done :) */

// throw new \Exception("Error Processing Request", 1);

user_error('Something happend!', 2);
