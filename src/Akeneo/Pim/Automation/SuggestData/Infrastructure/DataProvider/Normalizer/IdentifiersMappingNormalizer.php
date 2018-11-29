<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * Normalizes an IdentifiersMapping for API.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingNormalizer
{
    /**
     * @param IdentifiersMapping $mapping
     *
     * @return array
     */
    public function normalize(IdentifiersMapping $mapping): array
    {
        $normalizedMapping = [];
        foreach (IdentifiersMapping::FRANKLIN_IDENTIFIERS as $franklinIdentifier) {
            $normalizedMapping[$franklinIdentifier] = [
                'from' => ['id' => $franklinIdentifier],
                'status' => 'inactive',
                'to' => null,
            ];
        }

        foreach ($mapping->getIdentifiers() as $identifier => $attribute) {
            if (null !== $attribute) {
                $labels = [];
                $translations = $attribute->getTranslations();
                if (!$translations->isEmpty()) {
                    foreach ($attribute->getTranslations() as $translation) {
                        $labels[$translation->getLocale()] = $translation->getLabel();
                    }
                }

                $normalizedMapping[$identifier]['status'] = 'active';
                $normalizedMapping[$identifier]['to'] = [
                    'id' => $attribute->getCode(),
                    'label' => $labels,
                ];
            }
        }

        return array_values($normalizedMapping);
    }
}
