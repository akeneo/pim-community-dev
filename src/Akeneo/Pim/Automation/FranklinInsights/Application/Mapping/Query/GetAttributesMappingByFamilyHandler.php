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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandler
{
    /** @var AttributesMappingProviderInterface */
    private $attributesMappingProvider;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /**
     * @param AttributesMappingProviderInterface $attributesMappingProvider
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        AttributesMappingProviderInterface $attributesMappingProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param GetAttributesMappingByFamilyQuery $query
     *
     * @throws DataProviderException
     *
     * @return AttributesMappingResponse
     */
    public function handle(GetAttributesMappingByFamilyQuery $query): AttributesMappingResponse
    {
        $this->ensureFamilyExists($query->getFamilyCode());

        $attributesMapping = $this->attributesMappingProvider->getAttributesMapping($query->getFamilyCode());

        return $this->unmapUnknownAttributes($attributesMapping);
    }

    /**
     * @param FamilyCode $familyCode
     */
    private function ensureFamilyExists(FamilyCode $familyCode): void
    {
        if (!$this->familyRepository->exist($familyCode)) {
            throw new \InvalidArgumentException(sprintf(
                'The family with code "%s" does not exist',
                $familyCode
            ));
        }
    }

    /**
     * @param AttributesMappingResponse $attributesMappingResponse
     *
     * @return AttributesMappingResponse
     */
    private function unmapUnknownAttributes(AttributesMappingResponse $attributesMappingResponse)
    {
        if ($attributesMappingResponse->isEmpty()) {
            return $attributesMappingResponse;
        }

        $unknownAttributeCodes = $this->computeUnknownAttributesCodes($attributesMappingResponse);

        if (empty($unknownAttributeCodes)) {
            return $attributesMappingResponse;
        }

        return $this->computeNewAttributesMapping($attributesMappingResponse, $unknownAttributeCodes);
    }

    /**
     * @param AttributesMappingResponse $attributesMappingResponse
     *
     * @return array
     */
    private function computeUnknownAttributesCodes(AttributesMappingResponse $attributesMappingResponse): array
    {
        $attributeCodesFromResponse = [];
        foreach ($attributesMappingResponse as $attributeMapping) {
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
     * @param AttributesMappingResponse $attributesMappingResponse
     * @param string[] $unknownAttributeCodes
     *
     * @return AttributesMappingResponse
     */
    private function computeNewAttributesMapping(AttributesMappingResponse $attributesMappingResponse, array $unknownAttributeCodes): AttributesMappingResponse
    {
        $newMapping = new AttributesMappingResponse();
        foreach ($attributesMappingResponse as $attributeMapping) {
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
