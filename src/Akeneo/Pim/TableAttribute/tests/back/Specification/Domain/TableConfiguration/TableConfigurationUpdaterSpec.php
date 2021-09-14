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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfigurationUpdater;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use PhpSpec\ObjectBehavior;

class TableConfigurationUpdaterSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository)
    {
        $columnFactory = new ColumnFactory([
            TextColumn::DATATYPE => TextColumn::class,
            SelectColumn::DATATYPE => SelectColumn::class,
            BooleanColumn::DATATYPE => BooleanColumn::class,
            NumberColumn::DATATYPE => NumberColumn::class,
        ]);

        $this->beConstructedWith($tableConfigurationRepository, $columnFactory);
    }

    function it_is_a_table_configuration_updater()
    {
        $this->shouldBeAnInstanceOf(TableConfigurationUpdater::class);
    }

    function it_adds_a_column_to_a_table_configuration(TableConfigurationRepository $tableConfigurationRepository)
    {
        $tableConfigurationRepository->getNextIdentifier(ColumnCode::fromString('quantity'))
            ->willReturn(ColumnId::fromString('quantity_99decf93-3121-461c-8e3c-539d175ca40b'));

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224', 'code' => 'ingredient']),
            TextColumn::fromNormalized(['id' => 'description_affb18c7-bd86-460d-98e5-c5bd0eb499ef', 'code' => 'description']),
        ]);

        $newTableConfiguration = $this->update($tableConfiguration, [
            ['code' => 'ingredient', 'data_type' => SelectColumn::DATATYPE],
            ['code' => 'description', 'data_type' => TextColumn::DATATYPE],
            ['code' => 'quantity', 'data_type' => NumberColumn::DATATYPE],
        ]);
        $newTableConfiguration->shouldNotBe($tableConfiguration);
        $newTableConfiguration->normalize()->shouldBeLike([
            [
                'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                'code' => 'ingredient',
                'data_type' => SelectColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
            [
                'id' => 'description_affb18c7-bd86-460d-98e5-c5bd0eb499ef',
                'code' => 'description',
                'data_type' => TextColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
            [
                'id' => 'quantity_99decf93-3121-461c-8e3c-539d175ca40b',
                'code' => 'quantity',
                'data_type' => NumberColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
        ]);
    }

    function it_removes_a_column_definition()
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(
                ['id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224', 'code' => 'ingredient']
            ),
            TextColumn::fromNormalized(
                ['id' => 'description_affb18c7-bd86-460d-98e5-c5bd0eb499ef', 'code' => 'description']
            ),
            BooleanColumn::fromNormalized(
                ['id' => 'is_allergenic_affb18c7-bd86-460d-98e5-c5bd0eb499ee', 'code' => 'is_allergenic'],
            )
        ]);

        $newTableConfiguration = $this->update($tableConfiguration, [
            ['code' => 'ingredient', 'data_type' => SelectColumn::DATATYPE],
            ['code' => 'description', 'data_type' => TextColumn::DATATYPE],
        ]);

        $newTableConfiguration->shouldNotBe($tableConfiguration);
        $newTableConfiguration->normalize()->shouldBeLike([
            [
                'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                'code' => 'ingredient',
                'data_type' => SelectColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
            [
                'id' => 'description_affb18c7-bd86-460d-98e5-c5bd0eb499ef',
                'code' => 'description',
                'data_type' => TextColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
        ]);
    }

    function it_updates_a_column_definition()
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(
                ['id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224', 'code' => 'ingredient']
            ),
            TextColumn::fromNormalized(
                ['id' => 'description_affb18c7-bd86-460d-98e5-c5bd0eb499ef', 'code' => 'description']
            ),
            BooleanColumn::fromNormalized(
                [
                    'id' => 'is_allergenic_affb18c7-bd86-460d-98e5-c5bd0eb499ee',
                    'code' => 'is_allergenic',
                    'labels' => ['fr_FR' => 'Allergène'],
                ],
            )
        ]);

        $newTableConfiguration = $this->update($tableConfiguration, [
            ['code' => 'ingredient', 'data_type' => SelectColumn::DATATYPE],
            ['code' => 'is_allergenic', 'data_type' => BooleanColumn::DATATYPE, 'labels' => ['en_US' => 'Allergenic']],
            ['code' => 'description', 'data_type' => TextColumn::DATATYPE],
        ]);

        $newTableConfiguration->shouldNotBe($tableConfiguration);
        $newTableConfiguration->normalize()->shouldBeLike([
            [
                'id' => 'ingredient_cf30d88f-38c9-4c01-9821-4b39a5e3c224',
                'code' => 'ingredient',
                'data_type' => SelectColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
            [
                'id' => 'is_allergenic_affb18c7-bd86-460d-98e5-c5bd0eb499ee',
                'code' => 'is_allergenic',
                'data_type' => BooleanColumn::DATATYPE,
                'labels' => ['en_US' => 'Allergenic'],
                'validations' => (object)[],
            ],
            [
                'id' => 'description_affb18c7-bd86-460d-98e5-c5bd0eb499ef',
                'code' => 'description',
                'data_type' => TextColumn::DATATYPE,
                'labels' => (object)[],
                'validations' => (object)[],
            ],
        ]);
    }
}
