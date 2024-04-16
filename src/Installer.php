<?php

namespace GrupoCometa\ClientOrchestrator;

class Installer
{
    public static function checksDependencies()
    {

        $dependens = [
            'cron',
            'ps',
            'top',
            'supervisorctl'
        ];

        $dependeNotIncludes = [];
        foreach ($dependens as  $depende) {
            exec("which $depende", $output, $returnVar);
            ConsoleLog::warning("$depende, $returnVar");
            if ($returnVar !== 0) {
                $dependeNotIncludes[] = $depende;
            }
        }

        if (!$dependeNotIncludes) return ConsoleLog::success("Todas dependencias instaladas com sucesso");

        $strDependNotFound = implode(', ', $dependeNotIncludes);
        ConsoleLog::error("Dependecias não localizadas [$strDependNotFound]");
        exit(1);
    }
}
