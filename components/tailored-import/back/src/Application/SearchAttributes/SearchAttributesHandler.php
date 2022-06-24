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

namespace Akeneo\Platform\TailoredImport\Application\SearchAttributes;

use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\FindAttributeTranslationsInterface;

class SearchAttributesHandler
{
    public function __construct(
        private FindAttributeTranslationsInterface $findAttributeTranslations,
    ) {
    }

    public function handle(SearchAttributesQuery $query): array
    {
        $attributeCodes = $query->attributeCodes;
        $localeCode = $query->localeCode;
        $search = $query->search;

        $attributeTranslations = $this->findAttributeTranslations->byAttributeCodesAndLocaleCode(
            $attributeCodes,
            $localeCode,
        );

        $matchingAttributeCodes = [];
        foreach ($attributeCodes as $code) {
            $label = $attributeTranslations[$code] ?? '';

            if (false !== stripos($code, $search) || false !== stripos($label, $search)) {
                $matchingAttributeCodes[] = $code;
            }
        }

        return $matchingAttributeCodes;
    }
}
