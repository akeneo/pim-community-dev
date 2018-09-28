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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidAttributeMappingTypeException;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidExternalAttributeTypeException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeMapping;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

class UpdateAttributesMappingByFamilyHandler
{
    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var DataProviderInterface */
    private $dataProvider;

    /**
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param DataProviderFactory $dataProviderFactory
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        DataProviderFactory $dataProviderFactory
    ) {
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->dataProvider = $dataProviderFactory->create();
    }

    /**
     * @param UpdateAttributesMappingByFamilyCommand $command
     */
    public function handle(UpdateAttributesMappingByFamilyCommand $command): void
    {
        $this->validate($command);

        $this->dataProvider->updateAttributesMapping($command->getFamilyCode(), $command->getAttributesMapping());
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
     * @param $pimAttribute
     */
    private function validateAttributeTypesMapping(AttributeMapping $attributeMapping, $pimAttribute): void
    {
        $attributeTypesMapping = [
            'metric' => AttributeTypes::METRIC,
            'select' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'multiselect' => AttributeTypes::OPTION_MULTI_SELECT,
            'number' => AttributeTypes::NUMBER,
            'text' => AttributeTypes::TEXT,
            'boolean' => AttributeTypes::BOOLEAN,
            'identifier' => AttributeTypes::IDENTIFIER,
        ];

        if (! array_key_exists($attributeMapping->getPimAiAttributeType(), $attributeTypesMapping)) {
            throw new InvalidExternalAttributeTypeException(sprintf(
                'The external attribute type "%" is unknown and cannot be mapped',
                $attributeMapping->getPimAiAttributeType()
            ));
        }

        if ($attributeTypesMapping[$attributeMapping->getPimAiAttributeType()] !== $pimAttribute->getType()) {
            throw new InvalidAttributeMappingTypeException(sprintf(
                'The external attribute type "%s" cannot be mapped to pim attribute type "%s"',
                $attributeMapping->getPimAiAttributeType(),
                $pimAttribute->getType()
            ));;
        }
    }
}
