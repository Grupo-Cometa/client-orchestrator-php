<?php

namespace GrupoCometa\ClientOrchestrator;

class ConsoleLog
{
    public static function success($message)
    {
        echo "\033[32m$message\033[0m\n";
    }

    public static function info($message)
    {
        echo "\033[34m$message\033[0m\n";
    }

    public static function error($message)
    {
        echo "\033[31m$message\033[0m\n";
    }

    public static function warning($message)
    {
        echo "\033[33m$message\033[0m\n";
    }
}
