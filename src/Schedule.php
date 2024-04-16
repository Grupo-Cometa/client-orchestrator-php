<?php

namespace GrupoCometa\ClientOrchestrator;

use JsonSerializable;

class Schedule implements JsonSerializable
{

    public function __construct(
        public string $action,
        public string $cronExpression,
        public string $robotPublicId,
        public int $scheduleId
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return $this;
    }
}
