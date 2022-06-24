<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Query\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\FindAttributeTranslationsInterface;

class FindAttributeTranslations implements FindAttributeTranslationsInterface
{
    public function __construct(
        private GetAttributeTranslations $getAttributeTranslations,
    ) {
    }

    public function byAttributeCodesAndLocaleCode(array $attributeCodes, string $localeCode): array
    {
        return $this->getAttributeTranslations->byAttributeCodesAndLocale(
            array_unique($attributeCodes),
            $localeCode,
        );
    }
}
