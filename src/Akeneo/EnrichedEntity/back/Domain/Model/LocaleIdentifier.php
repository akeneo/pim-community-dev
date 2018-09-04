<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model;

use Webmozart\Assert\Assert;

class LocaleIdentifier
{
    /** @var string */
    private $localeCode;

    private function __construct(string $localeCode)
    {
        Assert::notEmpty($localeCode, 'Locale code should not be empty');

        $this->localeCode = $localeCode;
    }

    public static function fromCode(string $code): self
    {
        return new self($code);
    }

    public function normalize(): string
    {
        return $this->localeCode;
    }

    public function equals(LocaleIdentifier $localeIdentifier): bool
    {
        return $localeIdentifier->localeCode === $this->localeCode;
    }
}
