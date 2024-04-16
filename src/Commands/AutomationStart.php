<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\Automation;
use GrupoCometa\ClientOrchestrator\Services\ExecutionAmqp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutomationStart  extends Command
{
    protected $signature = 'orchestrator:automation-start {publicId} {sheduleId?}';
    protected $description = 'Inicia uma automação';

    public function handle()
    {
        $automation = new Automation;
        $publicId = $this->argument('publicId');
        $instanceAutomation = $automation->getInstanceByPublicId($publicId);

        if (!$instanceAutomation) {
            return Log::error(
                "Class Automation não encontrada para o publicID $publicId, 
                verificar se extends AbstractAutomation"
            );
        }

        $executionAmqp = new ExecutionAmqp($instanceAutomation, $this->argument('sheduleId'));
        $executionAmqp->start();
        $instanceAutomation->start();
        $executionAmqp->stop();
    }
}
