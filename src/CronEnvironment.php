<?php

namespace GrupoCometa\ClientOrchestrator;

use Exception;

/**
 * O cron executa os jobs com o ambiente vazio, sem as variáveis do container
 * (AMQP_*, DB_*, ...). Este arquivo guarda as variáveis do processo atual para
 * o bash dos jobs carregar via BASH_ENV, que o CrontabScheduleManager define no crontab.
 */
class CronEnvironment
{
    private const IGNORED = ['HOME', 'PWD', 'OLDPWD', 'SHLVL', '_'];

    public static function path(): string
    {
        return config('orchestrator.cronEnvFile', '/etc/orchestrator.env');
    }

    public static function dump(): void
    {
        $lines = [];
        foreach (getenv() as $name => $value) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) continue;
            if (in_array($name, self::IGNORED, true) || str_starts_with($name, 'SUPERVISOR_')) continue;

            $lines[] = "export $name=" . self::quote($value);
        }

        $path = self::path();
        $tmp = $path . '.' . getmypid() . '.tmp';

        $umask = umask(0077);
        try {
            $written = file_put_contents($tmp, implode("\n", $lines) . "\n");
        } finally {
            umask($umask);
        }

        if ($written === false || !rename($tmp, $path)) {
            @unlink($tmp);
            throw new Exception("Não foi possível gravar as variáveis de ambiente do cron em $path");
        }
    }

    private static function quote(string $value): string
    {
        return "'" . str_replace("'", "'\\''", $value) . "'";
    }
}
