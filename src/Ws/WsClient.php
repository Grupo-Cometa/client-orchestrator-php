<?php

namespace GrupoCometa\ClientOrchestrator\Ws;

use Closure;
use GrupoCometa\ClientOrchestrator\ConsoleLog;
use Illuminate\Http\Client\ConnectionException;
use WebSocket\Client;

class WsClient
{
    private Client $socket;
    public function __construct(private string $channel)
    {
        $this->socket = new Client(config('orchestrator.wsUrl'));
    }

    public function send(array $data = [])
    {
        $message = json_encode([
            'type' => 'publish',
            'channel' => $this->channel,
            'data' => $data,
        ]);

        $this->socket->text($message);
    }

    public static function onMessage(string $channel, Closure $callback)
    {
        $socket = new Client(config('orchestrator.wsUrl'), [
            'timeout' => -1,
        ]);

        $messageSubs = json_encode([
            'type' => 'subscribe',
            'channel' => $channel,
        ]);


        $socket->text($messageSubs);

        ConsoleLog::success("Start listen web socket $channel...");
        while (true) {
            try {
                $message = json_decode($socket->receive());

                if (!$message) continue;
                if ($message->statusCode == 101) continue;
                $messageWs = new Message($message->statusCode, $message->data, $message->channel);
                $callback($messageWs);
            } catch (ConnectionException $e) {
                $socket->close();
                echo "ConnectionException: " . $e->getMessage() . "\n";
                sleep(5);

                self::onMessage($channel, $callback);
            } catch (\Throwable $th) {
                $socket->close();
                echo "Exception:" . $th->getMessage() . "\n";
                sleep(5);

                self::onMessage($channel, $callback);
            }
        }
    }

    public function close()
    {
        $this->socket->close();
    }
}
