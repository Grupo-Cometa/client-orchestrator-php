<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\ConsoleLog;
use GrupoCometa\ClientOrchestrator\Enums\EnumLogType;
use Illuminate\Support\Facades\Log as FacadesLog;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use WebSocket\Client;

class Log
{
    public function __construct(private string $publicId)
    {
    }

    private  function send(EnumLogType $logType, string $logMessage, $content)
    {
        try {
            $log = [
                'log_type' => $logType,
                'message' => $logMessage,
                'public_id' => $this->publicId,
                'type' => 'log',
                'date' => date('Y-m-d H:i:s')
            ];

            if ($content) {
                $log['content'] = $this->contentToBase64($content);
            }


            ConsoleLog::{$logType->value}($logMessage);

            self::wsSend($log);
            self::rabbitmqSend($log);
        } catch (\Exception $e) {
            ConsoleLog::error("erro enviar web socket Messge: {$e->getMessage()} File: {$e->getFile()}");
            FacadesLog::error("erro enviar web socket Messge: {$e->getMessage()} File: {$e->getFile()}");
        }
    }

    public  function info(string|int|float $message, mixed $content = null)
    {
        return $this->send(EnumLogType::INFO,  $message, $content);
    }

    public  function warning(string $message, mixed $content = null)
    {
        return $this->send(EnumLogType::WARNING,  $message, $content);
    }

    public  function success(string $message, mixed $content = null)
    {
        return $this->send(EnumLogType::SUCCESS,  $message, $content);
    }

    public  function error(string $message, mixed $content = null)
    {
        return $this->send(EnumLogType::ERROR,  $message, $content);
    }

    private  function wsSend($log)
    {
        $socket = new Client(config('orchestrator.wsUrl'));
        $message = json_encode([
            'type' => 'publish',
            'channel' => "logs." . $log["public_id"],
            'data' => $log
        ]);
        $socket->send($message);
        $socket->close();
    }

    private  function rabbitmqSend($log)
    {
        $queue = 'robots.executions-logs';
        $connection = new AMQPStreamConnection(
            config('orchestrator.rabbitmqHost'),
            config('orchestrator.rabbitmqPort'),
            config('orchestrator.rabbitmqUser'),
            config('orchestrator.rabbitmqPassword'),
        );

        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $message = new AMQPMessage(json_encode($log));
        $channel->basic_publish($message, '', $queue);
        $channel->close();
        $connection->close();
    }

    private function contentToBase64($content)
    {
        if (!$content) return null;
        return base64_encode($this->formatContent($content));
    }

    private function formatContent($content)
    {
        if (is_string($content)) return $this->fileOrStr($content);
 
        if (is_object($content) || is_array($content)) return json_encode($content);
       
        if (is_resource($content)) return stream_get_contents($content);
 
        $type = gettype($content);
        return json_encode(['erro' => "tipo de arquivo não suportador [$type]"]);
    }

    private function fileOrStr($content)
    {
        if (file_exists($content)) return file_get_contents($content);
        return $content;
    }
}
