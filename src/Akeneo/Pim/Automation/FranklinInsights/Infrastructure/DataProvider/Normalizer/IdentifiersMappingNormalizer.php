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

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * Normalizes an IdentifiersMapping for API.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingNormalizer
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

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

        $attributeCodes = [];
        foreach ($mapping->getMapping() as $identifierMapping) {
            if (!empty($identifierMapping->getAttributeCode())) {
                $attributeCodes[] = (string) $identifierMapping->getAttributeCode();
            }
        }

        $attributes = [];
        foreach ($this->attributeRepository->findByCodes($attributeCodes) as $attribute) {
            $attributes[(string) $attribute->getCode()] = $attribute;
        }

        foreach ($mapping->getMapping() as $franklinIdentifierCode => $identifier) {
            $attributeCode = $identifier->getAttributeCode();
            if (null !== $attributeCode) {
                $normalizedMapping[$franklinIdentifierCode]['status'] = 'active';
                $normalizedMapping[$franklinIdentifierCode]['to'] = [
                    'id' => (string) $attributeCode,
                    'label' => $attributes[(string) $attributeCode]->getLabels(),
                ];
            }
        }

        return array_values($normalizedMapping);
    }
}
