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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandler
{
    private $attributesMappingProvider;

    private $familyRepository;

    private $attributeRepository;

    public function __construct(
        AttributesMappingProviderInterface $attributesMappingProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function handle(GetAttributesMappingByFamilyQuery $query): AttributeMappingCollection
    {
        $this->ensureFamilyExists($query->getFamilyCode());

        $attributesMapping = $this->attributesMappingProvider->getAttributesMapping($query->getFamilyCode());

        return $this->unmapUnknownAttributes($attributesMapping);
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

    private function unmapUnknownAttributes(AttributeMappingCollection $attributeMappingCollection): AttributeMappingCollection
    {
        if ($attributeMappingCollection->isEmpty()) {
            return $attributeMappingCollection;
        }

        $unknownAttributeCodes = $this->computeUnknownAttributesCodes($attributeMappingCollection);

        if (empty($unknownAttributeCodes)) {
            return $attributeMappingCollection;
        }

        return $this->computeNewAttributesMapping($attributeMappingCollection, $unknownAttributeCodes);
    }

    private function computeUnknownAttributesCodes(AttributeMappingCollection $attributeMappingCollection): array
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

    /**
     * @param AttributeMappingCollection $attributeMappingCollection
     * @param string[] $unknownAttributeCodes
     *
     * @return AttributeMappingCollection
     */
    private function computeNewAttributesMapping(AttributeMappingCollection $attributeMappingCollection, array $unknownAttributeCodes): AttributeMappingCollection
    {
        $newMapping = new AttributeMappingCollection();
        foreach ($attributeMappingCollection as $attributeMapping) {
            $status = $attributeMapping->getStatus();
            $pimAttributeCode = $attributeMapping->getPimAttributeCode();
            if (in_array($attributeMapping->getPimAttributeCode(), $unknownAttributeCodes)) {
                $status = AttributeMappingStatus::ATTRIBUTE_PENDING;
                $pimAttributeCode = null;
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
}
