<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\Http\HttpOrchestrator;
use Illuminate\Support\Facades\Log;

class ResendSchedule
{
    public static function run(string $publicId)
    {
        sleep(60);

        try {
            $httpOrquestrator = new HttpOrchestrator;
            $robotId = $httpOrquestrator->getRobotIdByPublicId($publicId);
            $httpOrquestrator->resendSchedules($robotId);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
