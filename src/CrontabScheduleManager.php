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

    /**
     * Outro agendamento (id diferente) do mesmo robô com a mesma expressão.
     */
    private function existCronExpression(Schedule $schedule)
    {
        $text = (string) $this->getCronsText();
        $pattern = '/^' . preg_quote($schedule->cronExpression, '/')
            . '\s+.*orchestrator:automation-start ' . preg_quote($schedule->robotPublicId, '/') . ' (\d+)\s/m';

        preg_match_all($pattern, $text, $matches);
        foreach ($matches[1] as $scheduleId) {
            if ((int) $scheduleId !== $schedule->scheduleId) return true;
        }

        return false;
    }

    public function create(Schedule $schedule)
    {
        if ($this->existSchedule($schedule)) return;
        if ($this->existCronExpression($schedule)) {
            throw new Exception("A expressão cron já existe para outro agendamento deste robô");
        }

        $text = $this->getCronsText();
        $newTextCron = $text . $this->command($schedule);

        $this->write($newTextCron);
        if ($this->autoCommit) $this->commit();

        if (!$this->existSchedule($schedule)) {
            throw new Exception('Erro ao gravar agendamento');
        }
    }

    private function write($text)
    {
        $text = (string) $text;

        CronEnvironment::dump();
        if (!preg_match('/^BASH_ENV=/m', $text)) {
            $text = "SHELL=/bin/bash" . PHP_EOL . "BASH_ENV=" . CronEnvironment::path() . PHP_EOL . $text;
        }

        $baseTimezone = "TZ=America/Cuiaba";
        if (!str_contains($text, $baseTimezone)) {
            $text = $baseTimezone . PHP_EOL . $text;
        }

        if (!preg_match("/\n$/", $text)) {
            $text .= "\n";
        }

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
