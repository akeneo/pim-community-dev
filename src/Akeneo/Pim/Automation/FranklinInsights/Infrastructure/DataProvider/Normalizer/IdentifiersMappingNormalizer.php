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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;

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

        foreach ($mapping->getMapping() as $franklinIdentifierCode => $identifier) {
            $attribute = $identifier->getAttribute();
            if (null !== $attribute) {
                $normalizedMapping[$franklinIdentifierCode]['status'] = 'active';
                $normalizedMapping[$franklinIdentifierCode]['to'] = [
                    'id' => (string) $attribute->getCode(),
                    'label' => $attribute->getLabels(),
                ];
            }
        }

        return array_values($normalizedMapping);
    }
}
