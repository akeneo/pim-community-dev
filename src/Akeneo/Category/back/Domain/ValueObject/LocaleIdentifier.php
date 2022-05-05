<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

class LocaleIdentifier
{
    private function __construct(
        private string $identifier
    ) {
        Assert::notEmpty($identifier, 'Locale identifier should not be empty');
    }

    public static function fromCode(string $identifier): self
    {
        return new self($identifier);
    }

    public function normalize(): string
    {
        return $this->identifier;
    }

    public function equals(LocaleIdentifier $localeIdentifier): bool
    {
        return $localeIdentifier->identifier === $this->identifier;
    }
}
