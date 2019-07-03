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

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Psr\Log\LoggerInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class ApplyAttributeExactMatches
{
    private $selectExactMatchAttributeCodeQuery;

    private $saveAttributesMappingByFamilyHandler;

    private $logger;

    public function __construct(SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery, SaveAttributesMappingByFamilyHandler $saveAttributesMappingByFamilyHandler, LoggerInterface $logger)
    {
        $this->selectExactMatchAttributeCodeQuery = $selectExactMatchAttributeCodeQuery;
        $this->saveAttributesMappingByFamilyHandler = $saveAttributesMappingByFamilyHandler;
        $this->logger = $logger;
    }

    public function process(AttributeMappingCollection $attributeMappingCollection, FamilyCode $familyCode): AttributeMappingCollection
    {
        $matchedPimAttributeCodes = $this->findPimAttributeCodeMatches($familyCode, $attributeMappingCollection);

        $this->applyExactMatches($matchedPimAttributeCodes, $attributeMappingCollection);

        if ($this->exactMatchesHaveBeenApplied($attributeMappingCollection, $matchedPimAttributeCodes)) {
            $this->saveAttributeMapping($familyCode, $attributeMappingCollection);
        }

        return $attributeMappingCollection;
    }

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
        return array_filter($attributeCodes, function ($attributeCode) use ($attributeMappingCollection) {
            return null === $attributeCode || !$attributeMappingCollection->hasPimAttribute(new AttributeCode($attributeCode));
        });
    }

    private function applyExactMatches(array $matchedPimAttributeCodes, AttributeMappingCollection $attributeMappingCollection): void
    {
        foreach ($matchedPimAttributeCodes as $franklinLabel => $matchedPimAttributeCode) {
            foreach ($attributeMappingCollection as $attributeMapping) {
                if ($attributeMapping->getTargetAttributeLabel() === $franklinLabel) {
                    $attributeMappingCollection->applyExactMatchOnAttribute($attributeMapping->getTargetAttributeCode(), $matchedPimAttributeCode);
                }
            }
        }
    }

    private function saveAttributeMapping(FamilyCode $familyCode, AttributeMappingCollection $attributeMappingCollection): void
    {
        try {
            $this->saveAttributesMappingByFamilyHandler->handle(new SaveAttributesMappingByFamilyCommand(
                $familyCode,
                $attributeMappingCollection->formatForFranklin()
            ));
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('The attribute mapping can not be saved for family %s', (string) $familyCode),
                ['message' => $e->getMessage()]
            );
        }
    }

    private function exactMatchesHaveBeenApplied(AttributeMappingCollection $attributeMappingCollection, array $matchedPimAttributeCodes): bool
    {
        foreach ($attributeMappingCollection as $attributeMapping) {
            if (
                $attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_ACTIVE &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $matchedPimAttributeCodes) &&
                null !== $matchedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()]
            ) {
                return true;
            }
        }

        return false;
    }
}
