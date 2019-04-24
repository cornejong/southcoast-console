<?php

namespace SouthCoast\Console;

use SouthCoast\Helpers\ArrayHelper;
use SouthCoast\Helpers\Number;
use SouthCoast\Helpers\StringHelper;

/**
 * UNDER CONSTRUCTION
 */
class Cron
{
    const INTERVAL_FORMAT = [
        'week_day',
        'month',
        'day',
        'hour',
        'minute',
    ];

    const INTERVAL_ACCEPTED = [
        'week_day' => [0, 6],
        'month' => [1, 12],
        'day' => [1, 31],
        'hour' => [0, 23],
        'minute' => [0, 59],
    ];

    /**
     * @param string $command
     * @param array $interval
     * @param bool $is_php
     */
    public static function register(string $user, string $application, string $file, array $environment, array $interval)
    {
        /* Check if the interval is valid */
        if (!self::intervalIsValid($interval)) {
            throw new \Exception('Invalid Interval!', 1);
        }

        /* Check if the application is the full path to the app and if its valid */
        if (StringHelper::startsWith(DIRECTORY_SEPARATOR, $application) && !Validate::path($application)) {
            /* Path to application is invalid! */
            throw new \Exception('Path to application is invalid!', 1);
        }

        /* Check if the global application exists */
        if (!StringHelper::startsWith(DIRECTORY_SEPARATOR, $application) && empty(Console::run('command -v ' . $application))) {
            /* Command does not exists */
            throw new \Exception('Command does not exists!', 1);
        }

        return self::runProcess($application, $user, $file, $environment, $interval);
    }

    /**
     * @param Type $var
     */
    public static function runProcess(string $application, string $user, string $file, array $environment, array $interval)
    {
        $process = new Process(['logging' => true]);
        /* Set the command */
        $process->setCommand('crontab -e')
        /* Set the execution path */
            ->setExecutionPath(Console::pwd())
        /* Run the process */
            ->run();

        /* Write the cron */
        $process->write(implode(' ', $interval) . ' ' . $application . ' ' . $file . ' ' . self::stringifyEnvironment($environment));
        /* close the editor */
        Console::log($process->write(html_entity_decode('&#27;')));
        Console::log($process->write(':x'));
        Console::log($process->write(html_entity_decode('&#x2386;')));
        /* Read the response */
        $response = $process->read();
        /* Close the process */
        $process->close();

        return $response;
    }

    /**
     * @param Type $var
     */
    public static function terminate(string $identifier)
    {
        # code...
    }

    /**
     * @param array $environment
     */
    public static function stringifyEnvironment(array $environment): string
    {
        /* Create the response variable */
        $response = '';

        /* Loop over all the environment variables */
        foreach ($environment as $argument => $value) {
            /* Check if there is a key present */
            if (is_numeric($argument) && !empty($value)) {
                /* Just add the argument's value in quotes */
                $response .= '"' . $value . '"';
            }

            /* Check if we have an argument name and the value is not empty */
            if (!is_numeric($argument) && !empty($value)) {
                /* Add it to the response */
                $response .= $argument . '="' . $value . '"';
            }

            /* Check if we have an argument name and the value is empty */
            if (!is_numeric($argument) && empty($value)) {
                /* Just add the argument in quotes */
                $response .= '"' . $argument . '"';
            }

            /* Check if this is not the last element in the array */
            if ($argument !== ArrayHelper::lastKey($environment)) {
                /* Add a space, but only if it's not the last one */
                $response .= ' ';
            }
        }

        /* Return the response */
        return $response;
    }

    /**
     * @param array $interval
     */
    public static function intervalIsValid(array $interval): bool
    {
        /* First verify we have enough values */
        if (count($interval) !== 5) {
            throw new \Exception('Interval is in (or) over-complete! Expected 5 values, received: ' . count($interval), 1);
        }

        /* Give the interval some keys */
        $interval = array_combine(self::INTERVAL_FORMAT, $interval);

        /* Loop over all the values to check there validity */
        foreach ($interval as $type => $value) {
            if ($value === '*') {
                continue;
            }

            /* Check if the value is in the accepted range or if its a wild card */
            if (!Number::isInRange($value, self::INTERVAL_ACCEPTED[$type])) {
                /* If not, throw an exception */
                throw new \Exception('The value \'' . $value . '\' is outside the accepted range of: ' . implode(' => ', self::INTERVAL_ACCEPTED[$type]), 1);
            }

        }
        /* If nothing happened, we're all good! */
        return true;
    }
}
