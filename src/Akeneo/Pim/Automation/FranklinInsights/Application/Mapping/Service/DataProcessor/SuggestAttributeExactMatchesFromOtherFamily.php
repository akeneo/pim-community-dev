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
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectExactMatchAttributeCodeFromOtherFamilyQuery;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class SuggestAttributeExactMatchesFromOtherFamily implements AttributeMappingCollectionDataProcessorInterface
{
    private $selectExactMatchAttributeCodeFromOtherFamilyQuery;

    public function __construct(SelectExactMatchAttributeCodeFromOtherFamilyQuery $selectExactMatchAttributeCodeFromOtherFamilyQuery)
    {
        $this->selectExactMatchAttributeCodeFromOtherFamilyQuery = $selectExactMatchAttributeCodeFromOtherFamilyQuery;
    }

    public function process(AttributeMappingCollection $attributeMappingCollection, FamilyCode $familyCode): AttributeMappingCollection
    {
        $matchedPimAttributeCodes = $this->findPimAttributeCodeMatchesFromOtherFamily($familyCode, $attributeMappingCollection);

        return $this->buildAttributeMappingCollectionWithMatchedAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);
    }

    private function findPimAttributeCodeMatchesFromOtherFamily(FamilyCode $familyCode, AttributeMappingCollection $attributeMappingCollection): array
    {
        $matchedPimAttributeCodes = $this->selectExactMatchAttributeCodeFromOtherFamilyQuery->execute(
            $familyCode,
            $attributeMappingCollection->getPendingAttributesFranklinLabels()
        );

        return $this->filterNotMappedAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);
    }

    private function filterNotMappedAttributeCodes(array $attributeCodes, AttributeMappingCollection $attributeMappingCollection): array
    {
        return array_filter($attributeCodes, function ($attributeCode) use ($attributeMappingCollection) {
            return null === $attributeCode || !$attributeMappingCollection->hasPimAttribute(new AttributeCode($attributeCode));
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
