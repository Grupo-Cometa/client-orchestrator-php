<?php

namespace GrupoCometa\ClientOrchestrator;

use Closure;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Amqp
{
    public $channel;
    public $connection;
    public function __construct(private string $queue)
    {
        $config = config('orchestrator');
        $this->connection = new AMQPStreamConnection(
            $config['rabbitmqHost'],
            $config['rabbitmqPort'],
            $config['rabbitmqUser'],
            $config['rabbitmqPassword']
        );
        $this->channel = $this->connection->channel();
    }

    public function consume(Closure $callback)
    {
        $this->channel->queue_declare($this->queue, false, true, false, false);
        $this->channel->basic_consume($this->queue, '', false, true, false, false, $callback);
        $this->channel->consume();
    }

    public function publish(string $message)
    {
        $this->channel->queue_declare($this->queue, false, true, false, false);
        $msg = new AMQPMessage($message);
        $this->channel->basic_publish($msg, '', $this->queue);
    }
}
