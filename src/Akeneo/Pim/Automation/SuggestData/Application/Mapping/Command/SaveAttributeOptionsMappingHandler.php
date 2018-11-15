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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveAttributeOptionsMappingHandler
{
    /** @var DataProviderFactory */
    private $dataProviderFactory;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    /**
     * @param DataProviderFactory $dataProviderFactory
     * @param FamilyRepositoryInterface $familyRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     */
    public function __construct(
        DataProviderFactory $dataProviderFactory,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $this->dataProviderFactory = $dataProviderFactory;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * @param SaveAttributeOptionsMappingCommand $command
     */
    public function handle(SaveAttributeOptionsMappingCommand $command): void
    {
        $this->validate($command);

        $attributeOptionsMapping = new AttributeOptionsMapping();

        foreach ($command->attributeOptions() as $franklinOptionId => $attributeOption) {
            $attributeOptionsMapping->addAttributeOption(new AttributeOption(
                $franklinOptionId,
                $attributeOption->getFranklinAttributeOptionLabel(),
                $attributeOption->getPimAttributeOptionCode()
            ));
        }

        $dataProvider = $this->dataProviderFactory->create();
        $dataProvider->saveAttributeOptionsMapping(
            $command->familyCode(),
            $command->franklinAttributeId(),
            $attributeOptionsMapping
        );
    }

    /**
     * @param SaveAttributeOptionsMappingCommand $command
     */
    private function validate(SaveAttributeOptionsMappingCommand $command): void
    {
        $this->ensureFamilyExists((string) $command->familyCode());
        $this->ensureAttributeExists((string) $command->attributeCode());
        $this->ensureOptionsExistAndBelongToTheAttribute(
            (string) $command->attributeCode(),
            $command->attributeOptions()->getCatalogOptionCodes()
        );
    }

    /**
     * @param string $familyCode
     */
    private function ensureFamilyExists(string $familyCode): void
    {
        if (!$this->familyRepository->findOneByIdentifier($familyCode) instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf('Family "%s" does not exist', $familyCode)
            );
        }
    }

    /**
     * @param string $attributeCode
     */
    private function ensureAttributeExists(string $attributeCode): void
    {
        if (!$this->attributeRepository->findOneByIdentifier($attributeCode) instanceof AttributeInterface) {
            throw new \InvalidArgumentException(
                sprintf('Attribute "%s" does not exist', $attributeCode)
            );
        }
    }

    /**
     * @param string $attributeCode
     * @param array $optionCodes
     */
    private function ensureOptionsExistAndBelongToTheAttribute(string $attributeCode, array $optionCodes): void
    {
        $optionCodes = array_filter($optionCodes);
        $options = $this->attributeOptionRepository->findCodesByIdentifiers($attributeCode, $optionCodes);

        if (count($options) !== count($optionCodes)) {
            throw new \InvalidArgumentException(
                sprintf('Some options do not exist or do not belong to the attribute with code "%s"', $attributeCode)
            );
        }
    }
}
