<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\AbstractAutomation;
use GrupoCometa\ClientOrchestrator\Automation;
use Illuminate\Console\Command;


class Bootstrap  extends Command
{
    protected $signature = 'orchestrator:bootstrap';
    protected $description = 'Cria supervisor';

    public function handle()
    {
        $automation = new Automation;
        foreach ($automation->allNamespaces() as $classAutomation) {
            $instanceAutomation = new $classAutomation;
            if (!($instanceAutomation instanceof AbstractAutomation)) continue;

            $bindTemplate = $this->bindTemplete($instanceAutomation->publicId());
            $this->appendSupervisor($bindTemplate);
            $this->apply($instanceAutomation->publicId());
        }
    }

    private function apply($publicId)
    {
        shell_exec('supervisorctl reread');
        shell_exec('supervisorctl update');
        shell_exec("supervisorctl restart publish-status-$publicId");
        shell_exec("supervisorctl restart consume-schedules-$publicId");
    }

    private function appendSupervisor($bindTemplate)
    {
        if ($handle = fopen('/etc/supervisor/conf.d/supervisor.conf', 'a')) {
            fwrite($handle, PHP_EOL . PHP_EOL . $bindTemplate);
            fclose($handle);
        }
    }

    private function bindTemplete($publicId)
    {
        $template  = file_get_contents(__DIR__ . "/../templates/supervisor");
        return str_replace('{{publicId}}', $publicId, $template);
    }
}
