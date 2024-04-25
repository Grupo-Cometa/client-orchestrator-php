<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\Amqp;
use GrupoCometa\ClientOrchestrator\AbstractAutomation;
use stdClass;

class ExecutionAmqp
{
    private Amqp $amqp;

    public function __construct(
        private AbstractAutomation $automation,
        private string|int|null $scheduleId = null,
        private string|null $token = null,
        private stdClass|null $parameters = null
    ) {
        $this->amqp = new Amqp("robots.executions-logs");
    }

    public function start()
    {
        $this->amqp->publish($this->data('START'));
    }

    public function stop()
    {
        $this->amqp->publish($this->data('STOP'));
    }

    private function getParameters()
    {
        if ($this->parameters) return json_encode($this->parameters);
        return "{}";
    }

    private function data(string $status)
    {
        $data = [
            'type' => 'execution',
            'date' => date('Y-m-d H:i:s'),
            'status' => $status,
            'schedule_id' => $this->scheduleId,
            'parameters' =>  $this->getParameters(),
            'token' => $this->token ?? '',
            'public_id' => $this->automation->publicId()
        ];

        return json_encode($data);
    }
}
