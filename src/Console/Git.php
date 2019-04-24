<?php

namespace SouthCoast\Console;

class Git
{
    public static function isActiveRepo(): bool
    {
        $response = Console::run('git rev-parse --is-inside-work-tree --quiet 2>/dev/null');
        return !empty($response);
    }

    public static function getCurrentBranch()
    {
        return Console::run('git rev-parse --abbrev-ref HEAD');
    }

    public static function getRootDirectory()
    {
        return Console::run('git rev-parse --show-toplevel');
    }
}
