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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SaveAttributesMappingByFamilyHandler
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributesMappingProviderInterface */
    private $attributesMappingProvider;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributesMappingProviderInterface $attributesMappingProvider
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributesMappingProviderInterface $attributesMappingProvider,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param SaveAttributesMappingByFamilyCommand $command
     */
    public function handle(SaveAttributesMappingByFamilyCommand $command): void
    {
        $familyCode = $command->getFamilyCode();
        $attributesMapping = $command->getAttributesMapping();

        $attributesMapping = $this->filterUnknownAttributes($attributesMapping);
        if (empty($attributesMapping)) {
            throw AttributeMappingException::onlyUnknownMappedAttributes();
        }

        $this->validate($familyCode, $attributesMapping);

        $this->attributesMappingProvider->saveAttributesMapping(
            $familyCode,
            $attributesMapping
        );
        $this->subscriptionRepository->emptySuggestedDataAndMissingMappingByFamily($familyCode);
    }

    /**
     * @param array $attributesMapping
     *
     * @return array
     */
    private function filterUnknownAttributes(array $attributesMapping): array
    {
        $attributeCodes = [];
        foreach ($attributesMapping as $attributeMapping) {
            $attributeCodes[] = $attributeMapping->getPimAttributeCode();
        }

        $attributes = $this->attributeRepository->findBy(['code' => $attributeCodes]);

        return array_filter($attributesMapping, function ($attributeMapping) use ($attributes) {
            $attributeCode = $attributeMapping->getPimAttributeCode();
            if (null === $attributeCode) {
                return true;
            }

            foreach ($attributes as $attribute) {
                if ($attributeCode === $attribute->getCode()) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Validates that the family exists.
     * Validates that the attribute exists.
     *
     * @param string $familyCode
     * @param array $attributesMapping
     */
    private function validate(string $familyCode, array $attributesMapping): void
    {
        if (null === $this->familyRepository->findOneByIdentifier($familyCode)) {
            throw new \InvalidArgumentException(sprintf('Family "%s" not found', $familyCode));
        }

        foreach ($attributesMapping as $attributeMapping) {
            if (null !== $attributeMapping->getPimAttributeCode()) {
                $this->validateAndFillAttribute($attributeMapping);
            }
        }
    }

    /**
     * @param AttributeMapping $attributeMapping
     *
     * @throws AttributeMappingException
     */
    private function validateAndFillAttribute(AttributeMapping $attributeMapping): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeMapping->getPimAttributeCode());

        $this->validateAttributeType($attribute->getType());

        if ($attribute->isLocalizable()) {
            throw AttributeMappingException::localizableAttributeNotAllowed();
        }

        if ($attribute->isScopable()) {
            throw AttributeMappingException::scopableAttributeNotAllowed();
        }

        if ($attribute->isLocaleSpecific()) {
            throw AttributeMappingException::localeSpecificAttributeNotAllowed();
        }

        $attributeMapping->setAttribute($attribute);
    }

    /**
     * @param string $attributeType
     *
     * @throws AttributeMappingException
     */
    private function validateAttributeType(string $attributeType): void
    {
        $authorizedPimAttributeTypes = array_keys(AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS);
        if (!in_array($attributeType, $authorizedPimAttributeTypes)) {
            throw AttributeMappingException::incompatibleAttributeTypeMapping($attributeType);
        }
    }
}
