<?php

namespace GrupoCometa\ClientOrchestrator\Services;

use GrupoCometa\ClientOrchestrator\Amqp;
use GrupoCometa\ClientOrchestrator\ConsoleLog;
use GrupoCometa\ClientOrchestrator\CrontabScheduleManager;
use GrupoCometa\ClientOrchestrator\Schedule;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class ConsumerQueueSchedule
{
    public static function run(string $publicId)
    {
        $queue = "robot.schedules.$publicId";
        $amqp = new Amqp($queue);
        ConsoleLog::success("Consumer start $queue");
        $amqp->consume(function (AMQPMessage $messageAmqp) {

            try {
                $data =  json_decode($messageAmqp->body);
                $schedule = new Schedule(
                    $data->action,
                    $data->cronExpression,
                    $data->robotPublicId,
                    $data->scheduleId
                );

                $instance = new self;
                $instance->{$schedule->action}($schedule);

                $amqpSuccess = new Amqp("robots.schedules-success");
                $strSchedule = json_encode($schedule);
                $amqpSuccess->publish($strSchedule);
                $amqpSuccess->channel->close();
                $amqpSuccess->connection->close();
            } catch (\Exception $e) {
                ConsoleLog::error($e->getMessage());
                Log::error($e);
            }
        });
    }

    private function create(Schedule $schedule)
    {
        $cronManeger = new  CrontabScheduleManager();
        $cronManeger->create($schedule);
    }

    private function delete(Schedule $schedule)
    {
        $cronManeger = new  CrontabScheduleManager;
        $cronManeger->delete($schedule);
    }
}
