<?php

namespace GrupoCometa\ClientOrchestrator\Commands;


class Register  
{
    public static function commands()
    {
        return [
            AutomationStart::class,
            Bootstrap::class,
            ConsumerQueueScheduleCommand::class,
            PublishStatusCommand::class
        ];
    }
}
