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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Query\SelectExactMatchAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class ApplyAttributeExactMatches implements AttributeMappingCollectionDataProcessorInterface
{
    use LoggerAwareTrait;

    /** @var SelectExactMatchAttributeCodeQueryInterface */
    private $selectExactMatchAttributeCodeQuery;
    /** @var SaveAttributesMappingByFamilyHandler */
    private $attributesMappingByFamilyHandler;

    /**
     * ApplyAttributeExactMatchesData constructor.
     * @param SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery
     * @param SaveAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler
     * @param LoggerInterface $logger
     */
    public function __construct(SelectExactMatchAttributeCodeQueryInterface $selectExactMatchAttributeCodeQuery, SaveAttributesMappingByFamilyHandler $attributesMappingByFamilyHandler, LoggerInterface $logger)
    {
        $this->selectExactMatchAttributeCodeQuery = $selectExactMatchAttributeCodeQuery;
        $this->attributesMappingByFamilyHandler = $attributesMappingByFamilyHandler;

        $this->setLogger($logger);
    }

    public function process(AttributeMappingCollection $attributeMappingCollection, array $context = []): AttributeMappingCollection
    {
        $familyCode = $context['familyCode'] ?? null;

        if (!$familyCode instanceof FamilyCode) {
            return $attributeMappingCollection;
        }

        $matchedPimAttributeCodes = $this->findPimAttributeCodeMatches($familyCode, $attributeMappingCollection);

        $processedAttributeMappingCollection = $this->buildAttributeMappingCollectionWithMatchedAttributeCodes($matchedPimAttributeCodes, $attributeMappingCollection);

        if ($this->exactMatchesHaveBeenApplied($attributeMappingCollection, $matchedPimAttributeCodes)) {
            $this->saveAttributeMapping($familyCode, $processedAttributeMappingCollection);
        }

        return $processedAttributeMappingCollection;
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
        return array_filter($attributeCodes, function ($attributeCode) use ($attributeMappingCollection) {
            return null === $attributeCode || !$attributeMappingCollection->hasPimAttribute(new AttributeCode($attributeCode));
        });
    }

    private function buildAttributeMappingCollectionWithMatchedAttributeCodes(array $matchedPimAttributeCodes, AttributeMappingCollection $attributeMappingCollection): AttributeMappingCollection
    {
        $newMapping = new AttributeMappingCollection();

        foreach ($attributeMappingCollection as $attributeMapping) {
            $pimAttributeCode = $attributeMapping->getPimAttributeCode();
            $status = $attributeMapping->getStatus();

            if (
                $attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_PENDING &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $matchedPimAttributeCodes)
            ) {
                $pimAttributeCode = $matchedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()];

                if (null !== $pimAttributeCode) {
                    $status = AttributeMappingStatus::ATTRIBUTE_ACTIVE;
                }
            }

            $newAttributeMapping = new AttributeMapping(
                $attributeMapping->getTargetAttributeCode(),
                $attributeMapping->getTargetAttributeLabel(),
                $attributeMapping->getTargetAttributeType(),
                $pimAttributeCode,
                $status,
                $attributeMapping->getSummary()
            );
            $newMapping->addAttribute($newAttributeMapping);
        }

        return $newMapping;
    }

    private function saveAttributeMapping(FamilyCode $familyCode, AttributeMappingCollection $attributeMappingCollection): void
    {
        try {
            $this->attributesMappingByFamilyHandler->handle(new SaveAttributesMappingByFamilyCommand(
                $familyCode,
                $attributeMappingCollection->formatForFranklin())
            );
        }
        catch (AttributeMappingException | DataProviderException $e) {
            $this->logger->error(
                sprintf('[Franklin Insights] The attribute mapping can not be saved for family %s', (string) $familyCode),
                ['message' => $e->getMessage()]
            );
        }
    }

    private function exactMatchesHaveBeenApplied(AttributeMappingCollection $attributeMappingCollection, array $matchedPimAttributeCodes): bool
    {
        foreach ($attributeMappingCollection as $attributeMapping) {
            if (
                $attributeMapping->getStatus() === AttributeMappingStatus::ATTRIBUTE_PENDING &&
                array_key_exists($attributeMapping->getTargetAttributeLabel(), $matchedPimAttributeCodes) &&
                null !== $matchedPimAttributeCodes[$attributeMapping->getTargetAttributeLabel()]
            ) {
                return true;
            }
        }

        return false;
    }
}
