<?php

namespace GrupoCometa\ClientOrchestrator\Ws;

use stdClass;

class Message
{
    public function __construct(
        public $statusCode,
        public stdClass $data,
        public $channel
    ) {
    }
}
