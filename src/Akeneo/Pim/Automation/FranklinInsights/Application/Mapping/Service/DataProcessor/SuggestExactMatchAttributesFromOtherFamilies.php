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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectExactMatchAttributeCodesFromOtherFamiliesQuery;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SuggestExactMatchAttributesFromOtherFamilies
{
    private $selectExactMatchAttributeCodesFromOtherFamiliesQuery;

    public function __construct(SelectExactMatchAttributeCodesFromOtherFamiliesQuery $selectExactMatchAttributeCodesFromOtherFamiliesQuery)
    {
        $this->selectExactMatchAttributeCodesFromOtherFamiliesQuery = $selectExactMatchAttributeCodesFromOtherFamiliesQuery;
    }

    public function process(AttributeMappingCollection $attributeMappingCollection, FamilyCode $familyCode): AttributeMappingCollection
    {
        $matchedPimAttributeCodes = $this->findPimAttributeCodeMatchesFromOtherFamily($familyCode, $attributeMappingCollection);

        return $this->buildAttributeMappingCollectionWithMatchedAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);
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

    private function buildAttributeMappingCollectionWithMatchedAttributeCodes(array $matchedPimAttributeCodes, AttributeMappingCollection $attributeMappingCollection): AttributeMappingCollection
    {
        $newMapping = new AttributeMappingCollection();

        foreach ($attributeMappingCollection as $attributeMapping) {
            $exactMatchAttributeFromOtherFamily = null;

            if (
                $attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_PENDING &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $matchedPimAttributeCodes)
            ) {
                $exactMatchAttributeFromOtherFamily = $matchedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()];
            }

            $newAttributeMapping = new AttributeMapping(
                $attributeMapping->getTargetAttributeCode(),
                $attributeMapping->getTargetAttributeLabel(),
                $attributeMapping->getTargetAttributeType(),
                $attributeMapping->getPimAttributeCode(),
                $attributeMapping->getStatus(),
                $attributeMapping->getSummary(),
                $exactMatchAttributeFromOtherFamily
            );
            $newMapping->addAttribute($newAttributeMapping);
        }

        return $newMapping;
    }
}
