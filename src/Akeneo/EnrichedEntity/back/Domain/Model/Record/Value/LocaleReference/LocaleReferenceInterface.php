<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface LocaleReferenceInterface
{
    public function equals(LocaleReferenceInterface $localeReference): bool;
}
