<?php

namespace GrupoCometa\ClientOrchestrator;

use WebSocket\Client;

class WebSocketClient
{
    private $socket;
    public function __construct()
    {
        $this->socket = new Client(config('orchestrator.wsUrl'));
    }

    public function send($channel, array $data = [])
    {
        $message = json_encode([
            'type' => 'publish',
            'channel' => $channel,
            'data' => $data,
        ]);

        $this->socket->send($message);
    }

    public function close()
    {
        $this->socket->close();
    }
}
