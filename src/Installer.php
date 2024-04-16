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

        foreach ($dependens as  $depende) {
            exec("which $depende", $output, $returnVar);
            if ($returnVar !== 0) {
                throw new \RuntimeException("O $depende não está instalado ou não está funcionando corretamente.");
            }
        }
    }
}
