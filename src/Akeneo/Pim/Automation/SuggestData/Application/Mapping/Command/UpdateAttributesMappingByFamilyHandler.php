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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Exception\AttributeMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class UpdateAttributesMappingByFamilyHandler
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
     * @param UpdateAttributesMappingByFamilyCommand $command
     */
    public function handle(UpdateAttributesMappingByFamilyCommand $command): void
    {
        $this->validate($command);

        $this->attributesMappingProvider->updateAttributesMapping(
            $command->getFamilyCode(),
            $command->getAttributesMapping()
        );
        $this->subscriptionRepository->emptySuggestedDataAndMissingMappingByFamily($command->getFamilyCode());
    }

    /**
     * Validates that the family exists.
     * Validates that the attribute exists.
     *
     * @param UpdateAttributesMappingByFamilyCommand $command
     */
    private function validate(UpdateAttributesMappingByFamilyCommand $command): void
    {
        $familyCode = $command->getFamilyCode();
        if (null === $this->familyRepository->findOneByIdentifier($familyCode)) {
            throw new \InvalidArgumentException(sprintf('Family "%s" not found', $familyCode));
        }

        $attributesMapping = $command->getAttributesMapping();
        foreach ($attributesMapping as $attributeMapping) {
            if (null !== $attributeMapping->getPimAttributeCode()) {
                $this->validateAndFillAttribute($attributeMapping);
            }
        }
    }

    /**
     * @param AttributeMapping $attributeMapping
     */
    private function validateAndFillAttribute(AttributeMapping $attributeMapping): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeMapping->getPimAttributeCode());
        if (null === $attribute) {
            throw new \InvalidArgumentException(
                sprintf('Attribute "%s" not found', $attributeMapping->getPimAttributeCode())
            );
        }

        $this->validateAttributeType($attribute->getType());

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
