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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Webmozart\Assert\Assert;

class TableConfigurationUpdater
{
    private TableConfigurationRepository $tableConfigurationRepository;
    private ColumnFactory $columnFactory;

    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        ColumnFactory $columnFactory
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->columnFactory = $columnFactory;
    }

    /**
     * @param array<int, array<string, mixed>> $newRawTableConfiguration
     */
    public function update(TableConfiguration $tableConfiguration, array $newRawTableConfiguration): TableConfiguration
    {
        foreach ($newRawTableConfiguration as $index => $column) {
            Assert::stringNotEmpty($column['code']);
            $columnCode = ColumnCode::fromString($column['code']);
            $matchingColumn = $tableConfiguration->getColumnByCode($columnCode);
            $newRawTableConfiguration[$index]['id'] =
                (null === $matchingColumn || $matchingColumn->dataType()->asString() !== $column['data_type']) ?
                $this->tableConfigurationRepository->getNextIdentifier($columnCode)->asString() :
                $matchingColumn->id()->asString();
        }

        return TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $row): ColumnDefinition => $this->columnFactory->createFromNormalized($row),
                $newRawTableConfiguration
            )
        );
    }
}
