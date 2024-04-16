<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\AbstractAutomation;
use GrupoCometa\ClientOrchestrator\Automation;
use Illuminate\Console\Command;


class Bootstrap  extends Command
{
    protected $signature = 'orchestrator:bootstrap';
    protected $description = 'Cria supervisor';

    private $filenameSupervisor = '/etc/supervisor/conf.d/supervisor.conf';
    public function handle()
    {
        $this->initFileSupervisor();
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

    private function initFileSupervisor()
    {
        if (!is_dir('/var/log/automation/')) mkdir('/var/log/automation/', 0667, true);
        if (!file_exists($this->filenameSupervisor)) file_put_contents($this->filenameSupervisor, '');

        $currentContent = file_get_contents($this->filenameSupervisor);
        $exists = preg_match_all('/\[supervisord\]/', $currentContent, $matchs);
        if ($exists) return;

        $template = file_get_contents(__DIR__ . "/../templates/header");
        $newContent = $template . PHP_EOL . $currentContent;
        file_put_contents($this->filenameSupervisor, $newContent);
    }

    private function appendSupervisor($bindTemplate)
    {
        if ($handle = fopen($this->filenameSupervisor, 'a')) {
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
