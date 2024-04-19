<?php

namespace GrupoCometa\ClientOrchestrator\Commands;

use GrupoCometa\ClientOrchestrator\Automation;
use GrupoCometa\ClientOrchestrator\ConsoleLog;
use GrupoCometa\ClientOrchestrator\Ws\Message;
use GrupoCometa\ClientOrchestrator\Ws\WsClient;
use Illuminate\Console\Command;
use stdClass;

class ListenStartCommand  extends Command
{
    protected $signature = 'orchestrator:listen-start {publicId}';
    protected $description = 'Levanta connecção web socket para ouvir evento de start manual das automações';

    public function handle()
    {
        $publicId = $this->argument('publicId');

        WsClient::onMessage("start.$publicId", function (Message $message) use ($publicId) {
            ConsoleLog::info("Automaiton start Manual $publicId");
            $automation = new Automation;
            $automation->start(
                publicId: $publicId,
                token: $message->data->token,
                parameters: $message->data->parameters
            );
        });
    }
}
