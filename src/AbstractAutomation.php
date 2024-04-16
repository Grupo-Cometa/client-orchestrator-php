<?php


namespace GrupoCometa\ClientOrchestrator;

use GrupoCometa\ClientOrchestrator\Services\Log;

abstract class AbstractAutomation
{
    public Log $log;

    public function __construct()
    {
        $this->log = new Log($this->publicId());
    }

    abstract public  function publicId(): string;
    abstract public function start();
}
