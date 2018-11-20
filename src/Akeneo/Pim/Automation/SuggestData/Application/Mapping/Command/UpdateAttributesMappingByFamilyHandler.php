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
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class UpdateAttributesMappingByFamilyHandler
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributesMappingProviderInterface */
    private $attributesMappingProvider;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributesMappingProviderInterface $attributesMappingProvider
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributesMappingProviderInterface $attributesMappingProvider
    ) {
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributesMappingProvider = $attributesMappingProvider;
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
    }

    /**
     * Validates that the family exists
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

        $this->validateAttributeTypesMapping($attributeMapping, $attribute);

        $attributeMapping->setAttribute($attribute);
    }

    /**
     * @param AttributeMapping $attributeMapping
     * @param AttributeInterface $pimAttribute
     *
     * @throws AttributeMappingException
     */
    private function validateAttributeTypesMapping(
        AttributeMapping $attributeMapping,
        AttributeInterface $pimAttribute
    ): void {
        $expectedAttributeType = AttributeMapping::ATTRIBUTE_TYPES_MAPPING[$attributeMapping->getTargetAttributeType()];
        if ($pimAttribute->getType() !== $expectedAttributeType) {
            throw AttributeMappingException::incompatibleAttributeTypeMapping(
                $attributeMapping->getTargetAttributeType(),
                $pimAttribute->getType()
            );
        }
    }
}
