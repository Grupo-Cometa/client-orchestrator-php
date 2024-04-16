<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\Http\HttpOrchestrator;

class ResendSchedule
{
    public static function run(string $publicId)
    {
        sleep(60);

        $httpOrquestrator = new HttpOrchestrator;
        $robotId = $httpOrquestrator->getRobotIdByPublicId($publicId);
        $httpOrquestrator->resendSchedules($robotId);
    }
}
