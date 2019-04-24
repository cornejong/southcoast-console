<?php

file_put_contents(__DIR__ . '/cron_output.txt', 'Last Update: ' . date('Y-m-d H:i:s') . "\n" . 'ENV: ' . implode(', ' . $argv));
