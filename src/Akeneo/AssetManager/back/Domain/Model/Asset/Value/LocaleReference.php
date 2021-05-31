<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;

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
    private ?LocaleIdentifier $identifier = null;

    private function __construct(?LocaleIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromLocaleIdentifier(LocaleIdentifier $identifier): self
    {
        return new self($identifier) ;
    }

    public static function noReference(): self
    {
        return new self(null) ;
    }

    public static function createFromNormalized(?string $normalizedReference): self
    {
        if (null === $normalizedReference) {
            return LocaleReference::noReference();
        }

        return self::fromLocaleIdentifier(LocaleIdentifier::fromCode($normalizedReference));
    }

    public function equals(LocaleReference $localeReference): bool
    {
        if ($localeReference->isEmpty() && $this->isEmpty()) {
            return true;
        }
        if ($localeReference->isEmpty() !== $this->isEmpty()) {
            return false;
        }

        return $this->identifier->equals($localeReference->identifier);
    }

    public function getIdentifier(): LocaleIdentifier
    {
        return $this->identifier;
    }

    public function normalize(): ?string
    {
        if (null === $this->identifier) {
            return null;
        }

        return $this->identifier->normalize();
    }

    public function isEmpty(): bool
    {
        return null === $this->identifier;
    }
}
