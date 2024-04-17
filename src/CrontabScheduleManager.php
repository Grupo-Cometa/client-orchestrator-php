<?php

namespace GrupoCometa\ClientOrchestrator;

use Exception;
use Illuminate\Support\Facades\Log;

class CrontabScheduleManager
{
    private $username;
    private $autoCommit;
    private $folderLog = '/var/log/automation/';

    public function __construct($username = 'root', $autoCommit = true)
    {
        $this->username = $username;
        $this->autoCommit = $autoCommit;
        $this->createFolderLog();
    }

    private function createFolderLog()
    {
        if (is_dir($this->folderLog)) return;
        mkdir($this->folderLog, 0667);
    }

    public function getCronsText()
    {
        try {
            return shell_exec("crontab -u {$this->username} -l");
        } catch (Exception $e) {
            Log::error($e);
            return '';
        }
    }

    private function existSchedule(Schedule $schedule)
    {
        $text = $this->getCronsText();
        $pattern = "/#id={$schedule->scheduleId}\s/i";
        return preg_match($pattern, $text);
    }

    private function existCronExpression(Schedule $schedule)
    {
        $text = $this->getCronsText();
        $pattern = "/$schedule->cronExpression.*{$schedule->scheduleId}/i";
        return preg_match($pattern, $text);
    }

    public function create(Schedule $schedule)
    {
        if ($this->existSchedule($schedule)) return;
        if ($this->existCronExpression($schedule)) throw new Exception("A expressão cron ja existe para outro agendamento");

        $text = $this->getCronsText();
        $newTextCron = $text . $this->command($schedule);

        $this->write($newTextCron);
        if ($this->autoCommit) $this->commit();

        if (!$this->existSchedule($schedule)) {
            throw new Exception('erro ao gravar agendamento');
        }
    }

    private function write($text)
    {
        $baseTimezone = "TZ=America/Cuiaba";
        if (!str_contains($text, $baseTimezone)) $text = $baseTimezone . PHP_EOL . $text;

        $exists = preg_match("/\n$/", $text);

        if (!$exists) $text .= "\n";

        file_put_contents('/tmp/cron.txt', $text);

        shell_exec("crontab -u {$this->username} /tmp/cron.txt");
    }

    public function commit()
    {
        shell_exec('service cron restart');
    }

    private function command(Schedule $schedule)
    {
        return "{$schedule->cronExpression}  /usr/local/bin/php " . $this->getPathProject() . "/artisan orchestrator:automation-start {$schedule->robotPublicId} {$schedule->scheduleId} >> {$this->folderLog}{$schedule->robotPublicId}.log 2>&1 #id={$schedule->scheduleId} \n";
    }

    private function getPathProject()
    {
        return isset($_ENV['PATH_PROJECT']) ? $_ENV['PATH_PROJECT'] : '/var/www';
    }

    public function delete(Schedule $schedule)
    {
        $pattern = "/.*\s#id={$schedule->scheduleId}\s/i";
        $text = $this->getCronsText();
        $newTextCron = preg_replace($pattern, '', $text);

        $this->write($newTextCron);

        if ($this->autoCommit) $this->commit();
    }
}
