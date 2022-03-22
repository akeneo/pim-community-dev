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

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Ramsey\Uuid\Uuid;

class InMemoryTableConfigurationRepository implements TableConfigurationRepository
{
    private AttributeRepositoryInterface $attributeRepository;
    private TableConfigurationFactory $tableConfigurationFactory;

    public function __construct(AttributeRepositoryInterface $attributeRepository, TableConfigurationFactory $tableConfigurationFactory)
    {
        $this->attributeRepository = $attributeRepository;
        $this->tableConfigurationFactory = $tableConfigurationFactory;
    }

    public function getNextIdentifier(ColumnCode $columnCode): ColumnId
    {
        return ColumnId::createFromColumnCode($columnCode, Uuid::uuid4()->toString());
    }

    public function save(string $attributeCode, TableConfiguration $tableConfiguration): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            throw new \InvalidArgumentException(\sprintf('The "%s" attribute was not found', $attributeCode));
        }
        $attribute->setRawTableConfiguration($tableConfiguration->normalize());
    }

    public function getByAttributeCode(string $attributeCode): TableConfiguration
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute || AttributeTypes::TABLE !== $attribute->getType() || null === $attribute->getRawTableConfiguration()) {
            throw TableConfigurationNotFoundException::forAttributeCode($attributeCode);
        }

        return $this->tableConfigurationFactory->createFromNormalized(
            array_map(
                fn (array $row): array => [
                    'id' => $row['id'] ?? ColumnIdGenerator::generateAsString($row['code']),
                    'code' => $row['code'],
                    'data_type' => $row['data_type'],
                    'labels' => $row['labels'] ?? [],
                    'validations' => $row['validations'] ?? [],
                    'is_required_for_completeness' => $row['is_required_for_completeness'] ?? false,
                    'reference_entity_identifier' => $row['reference_entity_identifier'] ?? null,
                    'measurement_family_code' => $row['measurement_family_code'] ?? null,
                    'measurement_default_unit_code' => $row['measurement_default_unit_code'] ?? null,
                ],
                $attribute->getRawTableConfiguration()
            )
        );
    }
}
