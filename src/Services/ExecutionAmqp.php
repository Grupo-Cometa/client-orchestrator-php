<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\Amqp;
use GrupoCometa\ClientOrchestrator\AbstractAutomation;

class ExecutionAmqp
{
    private Amqp $amqp;

    public function __construct(private AbstractAutomation $automation, private string|int $scheduleId)
    {
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

    private function data(string $status)
    {
        $data = [
            'type' => 'execution',
            'date' => date('Y-m-d H:i:s'),
            'status' => $status,
            'schedule_id' => $this->scheduleId,
            'parameters' => "{}",
            'token' => '',
            'public_id' => $this->automation->publicId()
        ];

        return json_encode($data);
    }
}
