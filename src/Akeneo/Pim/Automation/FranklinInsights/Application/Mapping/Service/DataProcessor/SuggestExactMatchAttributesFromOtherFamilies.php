<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\DataProcessor;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodesFromOtherFamiliesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SuggestExactMatchAttributesFromOtherFamilies
{
    private $selectExactMatchAttributeCodesFromOtherFamiliesQuery;

    public function __construct(SelectExactMatchAttributeCodesFromOtherFamiliesQueryInterface $selectExactMatchAttributeCodesFromOtherFamiliesQuery)
    {
        $this->selectExactMatchAttributeCodesFromOtherFamiliesQuery = $selectExactMatchAttributeCodesFromOtherFamiliesQuery;
    }

    public function process(AttributeMappingCollection $attributeMappingCollection, FamilyCode $familyCode): AttributeMappingCollection
    {
        $matchedPimAttributeCodes = $this->findPimAttributeCodeMatchesFromOtherFamily($familyCode, $attributeMappingCollection);

        $this->applyAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);

        return $attributeMappingCollection;
    }

    private function findPimAttributeCodeMatchesFromOtherFamily(FamilyCode $familyCode, AttributeMappingCollection $attributeMappingCollection): array
    {
        $matchedPimAttributeCodes = $this->selectExactMatchAttributeCodesFromOtherFamiliesQuery->execute(
            $familyCode,
            $attributeMappingCollection->getPendingAttributesFranklinLabels()
        );

        return $this->filterValidMatchedAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);
    }

    /**
     * @param array[string] $matchedAttributeCodes
     * @param AttributeMappingCollection $attributeMappingCollection
     * @return array
     *
     * @example $matchedAttributeCodes = ['Matched Franklin label' => 'pim_matched_attribute_code', 'Color' => 'color', 'Not Matched Franklin label' => null, 'Weight' => null]
     *          returns: ['Matched Franklin label' => 'pim_matched_attribute_code', 'Color' => 'color']
     */
    private function filterValidMatchedAttributeCodes(array $matchedAttributeCodes, AttributeMappingCollection $attributeMappingCollection): array
    {
        return array_filter($matchedAttributeCodes, function ($attributeCode) use ($attributeMappingCollection) {
            return null !== $attributeCode && !$attributeMappingCollection->hasPimAttribute(new AttributeCode($attributeCode));
        });
    }

    private function applyAttributeCodes(array $matchedPimAttributeCodes, AttributeMappingCollection $attributeMappingCollection): void
    {
        foreach ($attributeMappingCollection as $attributeMapping) {
            if (
                $attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_PENDING &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $matchedPimAttributeCodes)
            ) {
                $attributeMappingCollection->applyExactMatchAttributeSuggestionFromOtherFamily(
                    $attributeMapping->getTargetAttributeCode(),
                    $matchedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()]
                );
            }
        }
    }
}
