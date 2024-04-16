<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\ConsoleLog;
use GrupoCometa\ClientOrchestrator\WebSocketClient;
use GrupoCometa\ClientOrchestrator\PrintConsole;

class PublishStatusWebSocket
{
    public static function run($publicId)
    {
        ConsoleLog::success("Publish status [$publicId] Start...");
        $socket = new WebSocketClient;
        while (true) {
            $socket->send("status.$publicId", [
                'inExecution' => self::inExecution($publicId),
                'cpu' => self::cpu(),
                'ram' => self::ram(),
                'versionClient' => '1.0'
            ]);

            sleep(2.5);
        }
    }

    private static function cpu()
    {
        exec('top -bn1 | grep "Cpu(s)" | awk "{print $2}"', $cpuUsage);
        return round((float) $cpuUsage[0], 2);
    }

    private static function inExecution($publicId): bool
    {
        $subPublicId = substr($publicId, 0, 12);
        $command = "ps aux | grep 'php artisan orchestrator:automation-start $subPublicId' | grep -v grep";
        exec($command, $inExecution);
        return !!count($inExecution);
    }

    private static function ram()
    {
        exec("free | awk '/Mem/{print  ($3/$2) * 100 }'", $percutalRam);
        return round((float) $percutalRam[0], 2);
    }
}
