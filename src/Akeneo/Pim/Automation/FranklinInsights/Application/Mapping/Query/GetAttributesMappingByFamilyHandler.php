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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectFamilyAttributeCodesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandler
{
    private $attributesMappingProvider;

    private $familyRepository;

    private $attributeRepository;

    private $selectFamilyAttributeCodesQuery;

    public function __construct(
        AttributesMappingProviderInterface $attributesMappingProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        SelectFamilyAttributeCodesQueryInterface $selectFamilyAttributeCodesQuery
    ) {
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->selectFamilyAttributeCodesQuery = $selectFamilyAttributeCodesQuery;
    }

    public function handle(GetAttributesMappingByFamilyQuery $query): AttributeMappingCollection
    {
        $this->ensureFamilyExists($query->getFamilyCode());

        $attributesMapping = $this->attributesMappingProvider->getAttributesMapping($query->getFamilyCode());

        return $this->unmapUnknownAttributes($query->getFamilyCode(), $attributesMapping);
    }

    private function ensureFamilyExists(FamilyCode $familyCode): void
    {
        if (!$this->familyRepository->exist($familyCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The family with code "%s" does not exist',
                $familyCode
            ));
        }
    }

    private function unmapUnknownAttributes(FamilyCode $familyCode, AttributeMappingCollection $attributeMappingCollection): AttributeMappingCollection
    {
        if ($attributeMappingCollection->isEmpty()) {
            return $attributeMappingCollection;
        }

        $unknownMappedAttributeCodes = $this->computeUnknownMappedAttributesCodes($attributeMappingCollection);
        $invalidSuggestedAttributeCodes = $this->computeInvalidSuggestedAttributeCodes($familyCode, $attributeMappingCollection);

        if (empty($unknownMappedAttributeCodes) && empty($invalidSuggestedAttributeCodes)) {
            return $attributeMappingCollection;
        }

        return $this->computeNewAttributesMapping($attributeMappingCollection, $unknownMappedAttributeCodes, $invalidSuggestedAttributeCodes);
    }

    private function computeUnknownMappedAttributesCodes(AttributeMappingCollection $attributeMappingCollection): array
    {
        $attributeCodesFromResponse = [];
        foreach ($attributeMappingCollection as $attributeMapping) {
            if (null !== $attributeMapping->getPimAttributeCode()) {
                $attributeCodesFromResponse[] = $attributeMapping->getPimAttributeCode();
            }
        }

        $attributesCodes = [];
        foreach ($this->attributeRepository->findByCodes($attributeCodesFromResponse) as $attribute) {
            $attributesCodes[] = (string) $attribute->getCode();
        }

        return array_diff($attributeCodesFromResponse, $attributesCodes);
    }

    private function computeNewAttributesMapping(AttributeMappingCollection $attributeMappingCollection, array $unknownAttributeCodes, array $invalidSuggestedAttributeCodes): AttributeMappingCollection
    {
        $newMapping = new AttributeMappingCollection();
        foreach ($attributeMappingCollection as $attributeMapping) {
            $status = $attributeMapping->getStatus();
            $pimAttributeCode = $attributeMapping->getPimAttributeCode();
            if (in_array($attributeMapping->getPimAttributeCode(), $unknownAttributeCodes)) {
                $status = AttributeMappingStatus::ATTRIBUTE_PENDING;
                $pimAttributeCode = null;
            }
            $suggestions = array_values(array_diff($attributeMapping->getSuggestions(), $invalidSuggestedAttributeCodes));
            $newAttributeMapping = new AttributeMapping(
                $attributeMapping->getTargetAttributeCode(),
                $attributeMapping->getTargetAttributeLabel(),
                $attributeMapping->getTargetAttributeType(),
                $pimAttributeCode,
                $status,
                $attributeMapping->getSummary(),
                $suggestions
            );
            $newMapping->addAttribute($newAttributeMapping);
        }

        return $newMapping;
    }

    private function computeInvalidSuggestedAttributeCodes(FamilyCode $familyCode, AttributeMappingCollection $attributeMappingCollection): array
    {
        $suggestionAttributeCodes = $attributeMappingCollection->getSuggestionAttributesCodes();
        if (empty($suggestionAttributeCodes)) {
            return [];
        }

        $familyAttributeCodes = $this->selectFamilyAttributeCodesQuery->execute($familyCode);
        $unknownSuggestionAttributeCodes = array_values(array_diff($suggestionAttributeCodes, $familyAttributeCodes));

        $incompatibleAttributeCodes = $this->computeIncompatibleAttributesWithFranklin($suggestionAttributeCodes);

        return array_merge($unknownSuggestionAttributeCodes, $incompatibleAttributeCodes);
    }

    private function computeIncompatibleAttributesWithFranklin(array $suggestionAttributeCodes): array
    {
        $attributeCodes = [];
        foreach ($this->attributeRepository->findByCodes($suggestionAttributeCodes) as $attribute) {
            if ($attribute->isLocalizable() || $attribute->isScopable() || $attribute->isLocaleSpecific() || !$attribute->isTypeAllowedInFranklin()) {
                $attributeCodes[] = (string)$attribute->getCode();
            }
        }

        return $attributeCodes;
    }
}
