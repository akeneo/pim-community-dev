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

namespace Specification\Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class TableConfigurationFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'text' => TextColumn::class,
            'number' => NumberColumn::class,
            'boolean' => BooleanColumn::class,
            'select' => SelectColumn::class,
            'reference_entity' => ReferenceEntityColumn::class,
            'measurement' => MeasurementColumn::class,
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableConfigurationFactory::class);
    }

    function it_returns_a_table_configuration()
    {
        $tableConfiguration = $this->createFromNormalized([
            [
                'id' => ColumnIdGenerator::ingredient(),
                'data_type' => 'select',
                'code' => 'ingredient',
                'labels' => [],
            ],
            [
                'id' => ColumnIdGenerator::quantity(),
                'data_type' => 'number',
                'code' => 'quantity',
                'labels' => [],
            ],
            [
                'id' => ColumnIdGenerator::isAllergenic(),
                'data_type' => 'boolean',
                'code' => 'is_allergenic',
                'labels' => [],
                'is_required_for_completeness' => true,
            ],
            [
                'id' => ColumnIdGenerator::record(),
                'data_type' => 'reference_entity',
                'code' => 'record',
                'labels' => [],
                'is_required_for_completeness' => true,
                'reference_entity_identifier' => 'entity',
            ],
            [
                'id' => ColumnIdGenerator::duration(),
                'data_type' => 'measurement',
                'code' => 'duration',
                'labels' => [],
                'is_required_for_completeness' => true,
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ],
        ]);
        $tableConfiguration->shouldHaveType(TableConfiguration::class);

        $ingredientColumn = $tableConfiguration->getColumn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $ingredientColumn->shouldHaveType(SelectColumn::class);
        $ingredientColumn->id()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $ingredientColumn->code()->shouldBeLike(ColumnCode::fromString('ingredient'));
        $ingredientColumn->isRequiredForCompleteness()->asBoolean()->shouldBe(true);

        $quantityColumn = $tableConfiguration->getColumn(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $quantityColumn->shouldHaveType(NumberColumn::class);
        $quantityColumn->id()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::quantity()));
        $quantityColumn->code()->shouldBeLike(ColumnCode::fromString('quantity'));
        $quantityColumn->isRequiredForCompleteness()->asBoolean()->shouldBe(false);

        $isAllergenicColumn = $tableConfiguration->getColumn(ColumnId::fromString(ColumnIdGenerator::isAllergenic()));
        $isAllergenicColumn->shouldHaveType(BooleanColumn::class);
        $isAllergenicColumn->id()->shouldBeLike(ColumnId::fromString(ColumnIdGenerator::isAllergenic()));
        $isAllergenicColumn->code()->shouldBeLike(ColumnCode::fromString('is_allergenic'));
        $isAllergenicColumn->isRequiredForCompleteness()->asBoolean()->shouldBe(true);

        $referenceEntityColumn = $tableConfiguration->getColumn(ColumnId::fromString(ColumnIdGenerator::record()));
        $referenceEntityColumn->shouldHaveType(ReferenceEntityColumn::class);
        $referenceEntityColumn->referenceEntityIdentifier()->asString()->shouldReturn('entity');

        $measurementColumn = $tableConfiguration->getColumn(ColumnId::fromString(ColumnIdGenerator::duration()));
        $measurementColumn->shouldHaveType(MeasurementColumn::class);
        $measurementColumn->measurementFamilyCode()->asString()->shouldReturn('family');
        $measurementColumn->measurementDefaultUnitCode()->asString()->shouldReturn('unit');
    }

    function it_always_set_the_first_column_as_required_for_completeness()
    {
        $tableConfiguration = $this->createFromNormalized([
            [
                'id' => ColumnIdGenerator::ingredient(),
                'data_type' => 'select',
                'code' => 'ingredient',
                'labels' => [],
                'is_required_for_completeness' => false,
            ],
            [
                'id' => ColumnIdGenerator::quantity(),
                'data_type' => 'number',
                'code' => 'quantity',
                'labels' => [],
            ],
        ]);
        $tableConfiguration->shouldHaveType(TableConfiguration::class);

        $ingredientColumn = $tableConfiguration->getColumn(ColumnId::fromString(ColumnIdGenerator::ingredient()));
        $ingredientColumn->shouldHaveType(SelectColumn::class);
        $ingredientColumn->isRequiredForCompleteness()->asBoolean()->shouldBe(true);
    }

    function it_throws_an_exception_when_data_type_is_not_provided()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createFromNormalized', [
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => [],
                ],
                [
                    'code' => 'quantities',
                    'labels' => [],
                ],
            ],
        ]);
    }

    function it_throws_an_exception_when_data_type_is_not_a_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createFromNormalized', [
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => [],
                ],
                [
                    'data_type' => ['text'],
                    'code' => 'quantities',
                    'labels' => [],
                ],
            ],
        ]);
    }

    function it_throws_an_exception_when_data_type_is_unknown()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('createFromNormalized', [
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => [],
                ],
                [
                    'data_type' => 'unknown',
                    'code' => 'quantities',
                    'labels' => [],
                ],
            ],
        ]);
    }
}
