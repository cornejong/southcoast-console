<?php

namespace SouthCoast\Console;

use ErrorException;
use SouthCoast\Console\Console;

class ErrorHandler
{
    const SEVERITY = [
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR',
        32 => 'E_CORE_WARNING',
        64 => 'E_COMPILE_ERROR',
        128 => 'E_COMPILE_WARNING',
        256 => 'E_USER_ERROR',
        512 => 'E_USER_WARNING',
        1024 => 'E_USER_NOTICE',
        2048 => 'E_STRICT',
        4096 => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
    ];

    /**
     * @var mixed
     */
    private static $log_to_file = false;
    /**
     * @var string
     */
    private static $log_directory = '';

    /**
     * @var string
     */
    protected static $application_root = null;

    public static function register()
    {
        set_exception_handler(__CLASS__ . '::ExceptionHandler');
        set_error_handler(__CLASS__ . '::ErrorHandler');
    }

    /**
     * @param $callback
     */
    public static function registerCustomErrorHandler($callback)
    {
        # code...
    }

    /**
     * @param $callback
     */
    public static function registerErrorLogger($callback)
    {
        # code...
    }

    /**
     * @param $callback
     */
    public static function registerErrorService($callback)
    {
        # code...
    }

    /**
     * @param string $directory
     */
    public static function setApplicationRoot(string $directory)
    {
        self::$application_root = $directory;
    }

    public static function applicationRoot(): string
    {
        return self::$application_root ?? app_root ?? '';
    }

    /**
     * @param \Throwable $th
     */
    public static function ExceptionHandler(\Throwable $th)
    {
        if (self::$log_to_file) {
            self::logExceptionToFile($th);
        }

        $error_header = Format::bold(' == ERROR == ' . ($th instanceof \ErrorException ? ErrorHandler::SEVERITY[$th->getCode()] : get_class($th)) . ' == ');

        Console::log(Console::EOL);
        Console::log(Color::red($error_header) . ' ' . $th->getMessage());
        Console::log('Trace:');

        list($scriptPath) = get_included_files();

        foreach (explode("\n", $th->getTraceAsString()) as $index => $trace) {
            if (preg_match('/\d*\s(.*)(\(\d*\)):\s(.*)$/', $trace, $matches)) {
                $trace = array_combine([
                    'all',
                    'path',
                    'line',
                    'method',
                ], $matches);

                Console::log($index . ': ' . str_replace(self::applicationRoot(), '', $trace['path']) . $trace['line'] . ' -> ' . $trace['method']);
            } elseif (preg_match('/\d*\s({main})/', $trace, $matches)) {
                $trace = array_combine(['all', 'origin'], $matches);
                Console::log($index . ': ' . str_replace(self::applicationRoot(), '', get_included_files()[0]) . ' -> Application Entry ' . $trace['origin']);
            } else {
                Console::log($trace);
            }
        }
        die($th->getCode());
    }

    /**
     * @param \Throwable $th
     */
    public static function logExceptionToFile(\Throwable $th)
    {
        # code...
    }

    /**
     * @param $err_severity
     * @param $err_msg
     * @param $err_file
     * @param $err_line
     * @param array $err_context
     */
    public static function ErrorHandler($err_severity, $err_msg, $err_file, $err_line, array $err_context = null)
    {
        if (0 === error_reporting()) {
            return false;
        }

        try {
            switch ($err_severity) {
                case E_ERROR:
                    throw new ErrorException($err_msg, E_ERROR, $err_severity, $err_file, $err_line);
                case E_WARNING:
                    throw new WarningException($err_msg, E_WARNING, $err_severity, $err_file, $err_line);
                case E_PARSE:
                    throw new ParseException($err_msg, E_PARSE, $err_severity, $err_file, $err_line);
                case E_NOTICE:
                    throw new NoticeException($err_msg, E_NOTICE, $err_severity, $err_file, $err_line);
                case E_CORE_ERROR:
                    throw new CoreErrorException($err_msg, E_CORE_ERROR, $err_severity, $err_file, $err_line);
                case E_CORE_WARNING:
                    throw new CoreWarningException($err_msg, E_CORE_WARNING, $err_severity, $err_file, $err_line);
                case E_COMPILE_ERROR:
                    throw new CompileErrorException($err_msg, E_COMPILE_ERROR, $err_severity, $err_file, $err_line);
                case E_COMPILE_WARNING:
                    throw new CoreWarningException($err_msg, E_COMPILE_WARNING, $err_severity, $err_file, $err_line);
                case E_USER_ERROR:
                    throw new UserErrorException($err_msg, E_USER_ERROR, $err_severity, $err_file, $err_line);
                case E_USER_WARNING:
                    throw new UserWarningException($err_msg, E_USER_WARNING, $err_severity, $err_file, $err_line);
                case E_USER_NOTICE:
                    throw new UserNoticeException($err_msg, E_USER_NOTICE, $err_severity, $err_file, $err_line);
                case E_STRICT:
                    throw new StrictException($err_msg, E_STRICT, $err_severity, $err_file, $err_line);
                case E_RECOVERABLE_ERROR:
                    throw new RecoverableErrorException($err_msg, E_RECOVERABLE_ERROR, $err_severity, $err_file, $err_line);
                case E_DEPRECATED:
                    throw new DeprecatedException($err_msg, E_DEPRECATED, $err_severity, $err_file, $err_line);
                case E_USER_DEPRECATED:
                    throw new UserDeprecatedException($err_msg, E_USER_DEPRECATED, $err_severity, $err_file, $err_line);
            }
        } catch (\Throwable $th) {
            self::ExceptionHandler($th);
        }
    }
}

class WarningException extends ErrorException
{}
class ParseException extends ErrorException
{}
class NoticeException extends ErrorException
{}
class CoreErrorException extends ErrorException
{}
class CoreWarningException extends ErrorException
{}
class CompileErrorException extends ErrorException
{}
class CompileWarningException extends ErrorException
{}
class UserErrorException extends ErrorException
{}
class UserWarningException extends ErrorException
{}
class UserNoticeException extends ErrorException
{}
class StrictException extends ErrorException
{}
class RecoverableErrorException extends ErrorException
{}
class DeprecatedException extends ErrorException
{}
class UserDeprecatedException extends ErrorException
{}
