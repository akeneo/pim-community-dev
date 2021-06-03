<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\Acceptance\InMemory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;

class InMemoryTableConfigurationRepository implements TableConfigurationRepository
{
    private AttributeRepositoryInterface $attributeRepository;
    private ColumnFactory $columnFactory;

    public function __construct(AttributeRepositoryInterface $attributeRepository, ColumnFactory $columnFactory)
    {
        $this->attributeRepository = $attributeRepository;
        $this->columnFactory = $columnFactory;
    }

    public function save(int $attributeId, TableConfiguration $tableConfiguration): void
    {
        $attribute = $this->attributeRepository->findOneBy(['id' => $attributeId]);
        if (null === $attribute) {
            throw new \InvalidArgumentException(\sprintf('Attribute with id %d not found', $attributeId));
        }
        $attribute->setRawTableConfiguration($tableConfiguration->normalize());
    }

    public function getByAttributeId(int $attributeId): TableConfiguration
    {
        $attribute = $this->attributeRepository->findOneBy(['id' => $attributeId]);
        if (null === $attribute || AttributeTypes::TABLE !== $attribute->getType(
            ) || null === $attribute->getRawTableConfiguration()) {
            throw TableConfigurationNotFoundException::forAttributeId($attributeId);
        }

        return TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $row): ColumnDefinition => $this->columnFactory->createFromNormalized(
                    [
                        'code' => $row['code'],
                        'data_type' => $row['data_type'],
                        'labels' => \json_decode($row['labels'] ?? '{}', true),
                    ]
                ),
                $attribute->getRawTableConfiguration()
            )
        );
    }

    public function getByAttributeCode(string $attributeCode): TableConfiguration
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute || AttributeTypes::TABLE !== $attribute->getType() || null === $attribute->getRawTableConfiguration()) {
            throw TableConfigurationNotFoundException::forAttributeCode($attributeCode);
        }

        return TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $row): ColumnDefinition => $this->columnFactory->createFromNormalized(
                    [
                        'code' => $row['code'],
                        'data_type' => $row['data_type'],
                        'labels' => \json_decode($row['labels'] ?? '{}', true),
                    ]
                ),
                $attribute->getRawTableConfiguration()
            )
        );
    }
}
