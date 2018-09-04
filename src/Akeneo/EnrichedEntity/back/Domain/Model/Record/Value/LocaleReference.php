<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;

/**
 * A LocaleReference expresses a link to a locale.
 *
 * If there is one, then the locale reference is wrapping LocaleIdentifier
 * If it has no link then it is null.
 *
 * @see LocaleIdentifier
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleReference
{
    /** @var LocaleIdentifier|null */
    private $localeIdentifier;

    private function __construct(?LocaleIdentifier $localeIdentifier)
    {
        $this->localeIdentifier = $localeIdentifier;
    }

    public static function fromLocaleIdentifier(LocaleIdentifier $localeIdentifier): self
    {
        return new self($localeIdentifier) ;
    }

    public static function noLocale(): self
    {
        return new self(null) ;
    }

    public function equals(LocaleReference $localeReference): bool
    {
        if (null === $localeReference->localeIdentifier && null === $this->localeIdentifier) {
            return true;
        }
        if (null === $localeReference->localeIdentifier || null === $this->localeIdentifier) {
            return false;
        }

        return $this->localeIdentifier->equals($localeReference->localeIdentifier);
    }

    public function normalize(): ?string
    {
        if (null === $this->localeIdentifier) {
            return null;
        }

        return $this->localeIdentifier->normalize();
    }

    public function isEmpty(): bool
    {
        return null === $this->localeIdentifier;
    }
}
