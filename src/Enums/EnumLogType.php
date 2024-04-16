<?php

namespace GrupoCometa\ClientOrchestrator\Enums;

enum EnumLogType: string
{
    case SUCCESS = 'success';
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
}