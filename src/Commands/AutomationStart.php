<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\Automation;
use Illuminate\Console\Command;

class AutomationStart  extends Command
{
    protected $signature = 'orchestrator:automation-start {publicId} {sheduleId?}';
    protected $description = 'Inicia uma automação';

    public function handle()
    {
        $publicId = $this->argument('publicId');
        $scheduleId = $this->argument('sheduleId');

        $automation = new Automation;
        $automation->start($publicId, $scheduleId);
    }
}
