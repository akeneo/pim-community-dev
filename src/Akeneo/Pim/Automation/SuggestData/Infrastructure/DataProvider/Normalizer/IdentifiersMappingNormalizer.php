<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;

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
        foreach ($mapping->getIdentifiers() as $identifier => $attribute) {
            if (null !== $attribute) {
                $labels = [];
                $translations = $attribute->getTranslations();
                if (!$translations->isEmpty()) {
                    foreach ($attribute->getTranslations() as $translation) {
                        $labels[$translation->getLocale()] = $translation->getLabel();
                    }
                }

                $normalizedMapping[] = [
                    'from' => ['id' => $identifier],
                    'to' => [
                        'id' => $attribute->getCode(),
                        'label' => $labels,
                    ],
                ];
            }
        }

        return $normalizedMapping;
    }
}
