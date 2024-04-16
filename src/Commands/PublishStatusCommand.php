<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\Services\PublishStatusWebSocket;
use Illuminate\Console\Command;


class PublishStatusCommand  extends Command
{
    protected $signature = 'orchestrator:publish-status {publicId}';
    protected $description = 'Publica web socket  de status com cpu, ram, version cliente e execução';

    public function handle()
    {
        $publicId = $this->argument('publicId');
        PublishStatusWebSocket::run($publicId);
    }
}
