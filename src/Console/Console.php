<?php

namespace SouthCoast\Console;

use SouthCoast\Helpers\StringHelper;

class Console
{
    const EOL = PHP_EOL;

    /**
     * @var array
     */
    protected static $config = [];

    /**
     * @var mixed
     */
    protected static $environment_arguments_map = null;
    /**
     * @var mixed
     */
    protected static $env = false;

    /**
     * @param $message
     * @param string $type
     * @param string $color
     */
    public static function log($message, string $type = 'message', string $color = 'default')
    {
        Console::log_to_console($message);
    }

    /**
     * @param $variable
     */
    public static function logVar($variable)
    {
        /* Start the output buffer */
        ob_start();
        /* Var dump the variable */
        var_dump($variable);
        /* Get the result and clean the buffer */
        $result = ob_get_clean();
        /* Log the result */
        Console::log_to_console($result);
    }

    /**
     * @param $message
     */
    public static function error($message)
    {
        Console::log_to_console(Color::red(' == ' . Format::bold('ERROR') . ' == ') . ' ' . $message . ' ' . Color::red(' == '));
    }

    /**
     * @param $message
     */
    public static function success($message)
    {
        Console::log_to_console(Color::green(' == ' . Format::bold('SUCCESS') . ' == ') . ' ' . $message . ' ' . Color::green(' == '));
    }

    /**
     * @param $message
     */
    public static function warning($message)
    {
        Console::log_to_console(Color::yellow(' == ' . Format::bold('WARNING') . ' == ') . ' ' . $message . ' ' . Color::yellow(' == '));
    }

    /**
     * @param $message
     */
    public static function notification($message)
    {
        Console::log_to_console(Color::blue(' == ' . Format::bold('NOTIFICATION') . ' == ') . ' ' . $message . ' ' . Color::blue(' == '));
    }

    /**
     * @param string $message
     */
    public static function log_to_console(string $message)
    {
        print $message . Console::EOL;
    }

    public static function clear()
    {
        return system('clear');
    }

    public static function pwd()
    {
        return exec('pwd');
    }

    function exit(int $status = null) {
        exit($status);
    }

    /**
     * @param string $command
     * @param array $environment
     * @param nullstring $execution_path
     * @param nullbool $logging
     * @return mixed
     */
    public static function run(string $command, array $environment = null, string $execution_path = null, bool $logging = false)
    {
        /* Create a new process */
        $process = new Process([
            'logging' => $logging,
        ]);
        /* Set the command */
        $process->setCommand($command)
        /* Set the env */
            ->setEnvironment($environment)
        /* Set the execution path */
            ->setExecutionPath($execution_path)
        /* Run the process */
            ->run();
        /* read the process data */
        $response = $process->read();
        /* Close the process */
        $process->close();
        /* unset the process */
        unset($process);
        /* Return the response */
        return $response;
    }

    /**
     * Create a symlink from the original to the new path
     *
     * @param string $original      The original file or directory
     * @param string $new           The new directory the symlink should be placed in
     */
    public static function symlink(string $original, string $new)
    {
        return Console::run('ln -s "' . $original . '" "' . $new . '"');
    }

    /**
     * @param string $directory
     */
    public static function mkdir(string $directory)
    {
        return Console::run('mkdir "' . $directory . '"');
    }

    public static function envIsProvided()
    {
        if (!Console::$env) {
            Console::loadEnv();
        }

        return !empty(self::$env);
    }

    /**
     * @param array $map
     */
    public static function setEnvMap(array $map)
    {
        self::$environment_arguments_map = $map;
        self::loadEnv();
    }

    /**
     * @param $name
     */
    public static function env($name = null)
    {
        if (!Console::$env) {
            Console::loadEnv();
        }

        if (is_null($name)) {
            return Console::$env;
        }

        return isset(Console::$env[$name]) ? Console::$env[$name] : null;
    }

    protected static function loadEnv()
    {
        /* Get the global $argv variable */
        global $argv;
        /* Unset the first item (file name) */
        unset($argv[0]);

        $name_map = self::$environment_arguments_map;

        $tmp = [];
        foreach ($argv as $index => $value) {
            if (StringHelper::contains('=', $value)) {
                /* explode the key value pair */
                $value_array = explode('=', $value);
                /* Strip the quotes from the value */
                $tmp[$value_array[0]] = ltrim(rtrim($value_array[1], '"'), '"');
            } elseif (StringHelper::startsWith('--', $value) || StringHelper::startsWith('-', $value)) {
                $tmp[str_replace('--', '', $value)] = true;
            } else {
                /* Check if we have names left */
                if (!empty($name_map)) {
                    /* add the first name to this value */
                    $tmp[array_shift($name_map)] = $value;
                } else {
                    /* Otherwise, don't mind */
                    $tmp[] = $value;
                }
            }
        }
        /* Store it in $env */
        Console::$env = $tmp;
    }

    /**
     * @param $question
     */
    public static function ask($question = '')
    {
        /* Read the user Input and add the question */
        $line = readline($question);
        /* Add the line to the history */
        readline_add_history($line);
        /* return the line */
        return $line;
    }

    /**
     * @param $questions
     */
    public static function get(...$questions)
    {
        $answers = [];

        foreach ($questions as $question) {
            $answers[$question] = Console::ask($question . ': ');
        }

        return count($answers) === 1 ? array_shift($answers) : $answers;
    }
}
