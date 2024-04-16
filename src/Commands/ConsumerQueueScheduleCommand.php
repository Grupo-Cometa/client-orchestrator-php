<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\Services\ConsumerQueueSchedule;
use GrupoCometa\ClientOrchestrator\Services\ResendSchedule;
use Illuminate\Console\Command;

class ConsumerQueueScheduleCommand  extends Command
{
    protected $signature = 'orchestrator:consume-schedules {publicId}';
    protected $description = 'Inicia AMQP de agendamentos';

    public function handle()
    {
        $publicId = $this->argument('publicId');
        ResendSchedule::run($publicId);
        ConsumerQueueSchedule::run($publicId);
    }
}
