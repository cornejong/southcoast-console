<?php

namespace SouthCoast\Console;

class Format
{
    /**
     * returns a bold string
     *
     * @param string $string
     * @return string
     */
    public static function bold(string $string): string
    {
        return "\033[1m" . $string . "\033";
    }

    /**
     * returns an underlined string
     *
     * @param string $string
     * @return string
     */
    public static function underline(string $string): string
    {
        return "\033[4m" . $string . "\033";
    }

    /**
     * returns a padded string with the number of spaces provided
     *
     * @param integer $spaces
     * @param string $string
     * @return string
     */
    public static function leftPad(int $spaces, string $string): string
    {
        # Set the $space & $i variables
        $space = "";
        # Loop the amount of spaces provided in $spaces
        for ($i = 0; $i < $spaces; $i++) {
            # Add a space to the $space variable
            $space .= " ";
        }
        # Return the space + the string
        return $space . $string;
    }

    /**
     * Returns a centered formatted string based on the number of columns
     *
     * @param integer $columns
     * @param string $string
     * @return string
     */
    public static function formatCenter(int $columns, string $string): string
    {
        # Subtract the string length from the number of columns
        $remain = $columns - strlen($string);
        # Check if the remainder is an even number
        if (Number::isEven($remain)) {
            # If so, Return the string with a left pad of half the remainder
            return (Format::leftPad(($remain / 2), $string));
        } else {
            # If not, subtract 1 from the remainder
            # and return the string with a left pad of half the remainder
            return (Format::leftPad((($remain - 1) / 2), $string));
        }
    }
}
