<?php

namespace GrupoCometa\ClientOrchestrator;

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
}
