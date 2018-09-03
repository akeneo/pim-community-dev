<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class NoLocale implements LocaleReferenceInterface
{
    private function __construct()
    {
    }

    public function equals(LocaleReferenceInterface $localeReference): bool
    {
        return $localeReference instanceof NoLocale;
    }

    public function normalize(): ?string
    {
        return null;
    }

    public static function create(): self
    {
        return new self();
    }
}
