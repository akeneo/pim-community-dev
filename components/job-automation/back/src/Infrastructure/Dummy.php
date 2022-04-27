<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure;

class Dummy
{
    public function __construct(
        private string $message,
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
