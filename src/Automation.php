<?php

namespace GrupoCometa\ClientOrchestrator;

use GrupoCometa\ClientOrchestrator\Services\ExecutionAmqp;
use Illuminate\Support\Facades\Log;
use stdClass;

class Automation
{
    public function allNamespaces()
    {
        $namespaceBase = config('orchestrator.namespace') ?? 'App\Orchestrator';
        $folder = str_replace('\\', '/', lcfirst($namespaceBase));
        $filenames = glob(base_path($folder) . "/*.php");
        $namespaceClasses = [];

        foreach ($filenames as $filename) {
            $class = pathinfo($filename, PATHINFO_FILENAME);
            $namespaceClasses[] = "$namespaceBase\\$class";
        }

        return $namespaceClasses;
    }

    public function getInstanceByPublicId(string $publicId)
    {
        foreach ($this->allNamespaces() as  $class) {
            $instanceAutomation = new $class;
            if (!($instanceAutomation instanceof AbstractAutomation)) continue;

            if ($instanceAutomation->publicId() == $publicId) return $instanceAutomation;
        }
    }

    public function start($publicId, $scheduleId = null, string|null $token = null, stdClass|null $parameters = null)
    {
        $instanceAutomation = $this->getInstanceByPublicId($publicId);

        if (!$instanceAutomation) {
            return Log::error(
                "Class Automation não encontrada para o publicID $publicId, 
                verificar se extends AbstractAutomation"
            );
        }

        $executionAmqp = new ExecutionAmqp($instanceAutomation, $scheduleId, $token, $parameters);
        $executionAmqp->start();
        $instanceAutomation->start();
        $executionAmqp->stop();
    }
}
