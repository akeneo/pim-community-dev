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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class ApplyAttributeExactMatches implements AttributeMappingCollectionDataProcessorInterface
{
    /** @var SelectExactMatchAttributeCodeQueryInterface */
    private $selectExactMatchAttributeCodeQuery;

    /**
     * ApplyAttributeExactMatchesData constructor.
     * @param SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
     */
    public function __construct(SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery)
    {
        $this->selectExactMatchAttributeCodeQuery = $selectExactMatchAttributeCodeQuery;
    }

    public function process(AttributeMappingCollection $attributeMappingCollection, array $context = []): AttributeMappingCollection
    {
        $familyCode = $context['familyCode'] ?? null;

        if (!$familyCode instanceof FamilyCode) {
            return $attributeMappingCollection;
        }

        $matchedPimAttributeCodes = $this->findPimAttributeCodeMatches($familyCode, $attributeMappingCollection);

        return $this->buildAttributeMappingCollectionWithMatchedAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);
    }

    /**
     * @param FamilyCode $familyCode
     * @param AttributeMappingCollection $familyAttributesMapping
     * @return string[]
     */
    private function findPimAttributeCodeMatches(FamilyCode $familyCode, AttributeMappingCollection $familyAttributesMapping): array
    {
        $matchedPimAttributeCodes = $this->selectExactMatchAttributeCodeQuery->execute(
            $familyCode,
            $familyAttributesMapping->getPendingAttributesFranklinLabels()
        );

        return $this->filterNotMappedAttributeCodes($matchedPimAttributeCodes, $familyAttributesMapping);
    }

    private function filterNotMappedAttributeCodes(array $attributeCodes, AttributeMappingCollection $attributeMappingCollection): array
    {
        return array_filter($attributeCodes, function ($attributeCode) use ($attributeMappingCollection) {;
            return null === $attributeCode || !$attributeMappingCollection->hasPimAttribute(new AttributeCode($attributeCode));
        });
    }

    private function buildAttributeMappingCollectionWithMatchedAttributeCodes(array $matchedPimAttributeCodes, AttributeMappingCollection $attributeMappingCollection)
    {
        $newMapping = new AttributeMappingCollection();

        foreach ($attributeMappingCollection as $attributeMapping) {
            $pimAttributeCode = $attributeMapping->getPimAttributeCode();
            if ($attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_PENDING &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $matchedPimAttributeCodes)
            ) {
                $pimAttributeCode = $matchedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()];
            }

            $newAttributeMapping = new AttributeMapping(
                $attributeMapping->getTargetAttributeCode(),
                $attributeMapping->getTargetAttributeLabel(),
                $attributeMapping->getTargetAttributeType(),
                $pimAttributeCode,
                $attributeMapping->getStatus(),
                $attributeMapping->getSummary()
            );
            $newMapping->addAttribute($newAttributeMapping);
        }

        return $newMapping;
    }
}
