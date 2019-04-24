<?php

namespace SouthCoast\Console;

class Color
{
    const COLOR_OPENER = "\033[";
    const COLOR_SEPARATOR = "m";
    const COLOR_CLOSER = "\033[0m";

    const COLORS = [
        'font' => [
            'black' => '0;30',
            'dark_gray' => '1;30',
            'blue' => '0;34',
            'light_blue' => '1;34',
            'green' => '0;32',
            'light_green' => '1;32',
            'cyan' => '0;36',
            'light_cyan' => '1;36',
            'red' => '0;31',
            'light_red' => '1;31',
            'purple' => '0;35',
            'light_purple' => '1;35',
            'brown' => '0;33',
            'yellow' => '1;33',
            'light_gray' => '0;37',
            'white' => '1;37',
        ],
        'background' => [
            'black' => '40',
            'red' => '41',
            'green' => '42',
            'yellow' => '43',
            'blue' => '44',
            'magenta' => '45',
            'cyan' => '46',
            'light_gray' => '47',
        ],
    ];

    /**
     * @param $string
     * @param string $font_color
     * @param nullstring $background_color
     * @return mixed
     */
    public static function add_color($string, string $font_color = null, string $background_color = null): string
    {
        /* Initialize the response string */
        $response = '';
        /* Create the colorized flag */
        $colorized = false;
        /* Loop over all the positions */
        foreach (['font', 'background'] as $position) {
            /* Check if the color is supported for the current position */
            if (!is_null(${$position . '_color'}) && isset(Color::COLORS[$position][${$position . '_color'}])) {
                /* if it is, start with the opener, add the color code and end with the separator */
                $response .= Color::COLOR_OPENER . Color::COLORS[$position][${$position . '_color'}] . Color::COLOR_SEPARATOR;
                /* Set the colorized flag to true */
                $colorized = true;
            }
        }
        /* Add the provided string and the color closer if we are colorized */
        $response .= $string . ($colorized ? Color::COLOR_CLOSER : '');
        /* Return the response string */
        return $response;
    }

    /**
     * @param string $message
     * @param bool $invert
     */
    public static function red(string $message, bool $invert = true): string
    {
        return self::add_color($message, $invert ? null : 'red', $invert ? 'red' : null);
    }

    /**
     * @param string $message
     * @param bool $invert
     */
    public static function green(string $message, bool $invert = true): string
    {
        return self::add_color($message, $invert ? null : 'green', $invert ? 'green' : null);
    }

    /**
     * @param string $message
     * @param bool $invert
     */
    public static function blue(string $message, bool $invert = true): string
    {
        return self::add_color($message, $invert ? null : 'light_blue', $invert ? 'blue' : null);
    }

    /**
     * @param string $message
     * @param bool $invert
     */
    public static function yellow(string $message, bool $invert = false): string
    {
        return self::add_color($message, $invert ? null : 'yellow', $invert ? 'yellow' : null);
    }
}
