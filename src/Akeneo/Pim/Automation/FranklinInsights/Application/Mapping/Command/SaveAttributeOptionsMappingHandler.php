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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Exception\AttributeOptionsMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Query\SelectAttributeOptionCodesByIdentifiersQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveAttributeOptionsMappingHandler
{
    /** @var AttributeOptionsMappingProviderInterface */
    private $mappingProvider;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var SelectAttributeOptionCodesByIdentifiersQueryInterface */
    private $selectAttributeOptionCodesByIdentifiersQuery;

    public function __construct(
        AttributeOptionsMappingProviderInterface $mappingProvider,
        FamilyRepositoryInterface $familyRepository,
        AttributeRepositoryInterface $attributeRepository,
        SelectAttributeOptionCodesByIdentifiersQueryInterface $selectAttributeOptionCodesByIdentifiersQuery
    ) {
        $this->mappingProvider = $mappingProvider;
        $this->familyRepository = $familyRepository;
        $this->attributeRepository = $attributeRepository;
        $this->selectAttributeOptionCodesByIdentifiersQuery = $selectAttributeOptionCodesByIdentifiersQuery;
    }

    /**
     * @param SaveAttributeOptionsMappingCommand $command
     *
     * @throws AttributeOptionsMappingException
     */
    public function handle(SaveAttributeOptionsMappingCommand $command): void
    {
        $this->validate($command);

        $optionCodes = $this->getOptionCodes($command);
        if (empty($optionCodes)) {
            throw AttributeOptionsMappingException::emptyAttributeOptionsMapping();
        }

        $attributeOptionsMapping = new AttributeOptionsMapping($command->attributeCode());
        foreach ($command->attributeOptions() as $franklinOptionId => $attributeOption) {
            $optionCode = in_array($attributeOption->getPimAttributeOptionCode(), $optionCodes, true)
                ? $attributeOption->getPimAttributeOptionCode() : null;

            $attributeOptionsMapping->addAttributeOption(new AttributeOption(
                $franklinOptionId,
                $attributeOption->getFranklinAttributeOptionLabel(),
                $optionCode
            ));
        }

        $this->mappingProvider->saveAttributeOptionsMapping(
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
        $this->ensureFamilyExists($command->familyCode());
        $this->ensureAttributeExists((string) $command->attributeCode());
    }

    private function ensureFamilyExists(FamilyCode $familyCode): void
    {
        if (!$this->familyRepository->exist($familyCode)) {
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
        if (!$this->attributeRepository->findOneByIdentifier($attributeCode) instanceof Attribute) {
            throw new \InvalidArgumentException(
                sprintf('Attribute "%s" does not exist', $attributeCode)
            );
        }
    }

    /**
     * @param SaveAttributeOptionsMappingCommand $command
     *
     * @return array
     */
    private function getOptionCodes(SaveAttributeOptionsMappingCommand $command): array
    {
        return $this->selectAttributeOptionCodesByIdentifiersQuery->execute(
            (string) $command->attributeCode(),
            $command->attributeOptions()->getCatalogOptionCodes()
        );
    }
}
