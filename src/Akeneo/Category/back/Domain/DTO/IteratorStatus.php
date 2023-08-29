<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\DTO;

final class IteratorStatus
{
    private function __construct(
        public readonly bool $isDone,
        public readonly mixed $value = null,
    ) {
    }

    public static function inProgress(): self
    {
        return new self(false);
    }

    public static function done(mixed $value = null): self
    {
        return new self(true, $value);
    }
}
