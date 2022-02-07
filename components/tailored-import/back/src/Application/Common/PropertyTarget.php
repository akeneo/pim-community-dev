<?php

namespace Akeneo\Platform\TailoredImport\Application\Common;

class PropertyTarget implements TargetInterface
{
    const TYPE = 'property';

    private function __construct(
        private string $code,
        private string $action, 
        private string $ifEmpty)
    {
    }

    public static function createFromNormalized(array $normalizedPropertyTarget)
    {
        return new self(
            $normalizedPropertyTarget['code'],
            $normalizedPropertyTarget['action'],
            $normalizedPropertyTarget['if_empty']
        );
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAction(): string
    {
        return $this->action;
    }


    public function getIfEmpty(): string
    {
        return $this->ifEmpty;
    }
}