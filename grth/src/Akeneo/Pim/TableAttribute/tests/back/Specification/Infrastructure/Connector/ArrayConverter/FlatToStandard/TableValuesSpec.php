<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\ArrayConverter\FlatToStandard\TableValues;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use PhpSpec\ObjectBehavior;

class TableValuesSpec extends ObjectBehavior
{
    function let(
        FieldsRequirementChecker $fieldsRequirementChecker,
        GetAttributes $getAttributes,
        TableConfigurationRepository $tableConfigurationRepository
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'allergenic']),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::length(),
                'code' => 'length',
                'measurement_family_code' => 'length',
                'measurement_default_unit_code' => 'METER',
            ]),
        ]));

        $this->beConstructedWith($fieldsRequirementChecker, $getAttributes, $tableConfigurationRepository, 'product');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValues::class);
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_denormalizes_a_table_row(GetAttributes $getAttributes)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'nutrition',
            'ingredient' => 'salt',
            'quantity' => '50',
            'allergenic' => '0',
            'length' => '10 CENTIMETER',
        ];

        $getAttributes->forCode('nutrition')->willReturn($this->getTableAttribute('nutrition', false, false));

        $this->convert($item)->shouldBeLike([
            'entity' => '11111',
            'attribute_code' => 'nutrition',
            'locale' => null,
            'scope' => null,
            'row_values' => [
                'ingredient' => 'salt',
                'quantity' => '50',
                'allergenic' => false,
                'length' => [
                    'unit' => 'CENTIMETER',
                    'amount' => '10',
                ]
            ],
        ]);
    }

    function it_denormalizes_a_scopable_and_localizable_table_row(GetAttributes $getAttributes)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'nutrition-fr_FR-ecommerce',
            'ingredient' => 'salt',
            'quantity' => '50',
            'allergenic' => '1',
        ];

        $getAttributes->forCode('nutrition')->willReturn($this->getTableAttribute('nutrition', true, true));

        $this->convert($item)->shouldBeLike([
            'entity' => '11111',
            'attribute_code' => 'nutrition',
            'locale' => 'fr_FR',
            'scope' => 'ecommerce',
            'row_values' => [
                'ingredient' => 'salt',
                'quantity' => '50',
                'allergenic' => true,
            ],
        ]);
    }

    function it_denormalizes_a_scopable_table_row(GetAttributes $getAttributes)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'nutrition-ecommerce',
            'ingredient' => 'salt',
            'quantity' => '50',
            'allergenic' => '1',
        ];

        $getAttributes->forCode('nutrition')->willReturn($this->getTableAttribute('nutrition', false, true));

        $this->convert($item)->shouldBeLike([
            'entity' => '11111',
            'attribute_code' => 'nutrition',
            'locale' => null,
            'scope' => 'ecommerce',
            'row_values' => [
                'ingredient' => 'salt',
                'quantity' => '50',
                'allergenic' => true,
            ],
        ]);
    }

    function it_denormalizes_a_table_row_with_numeric_codes(
        TableConfigurationRepository $tableConfigurationRepository,
        GetAttributes $getAttributes
    ) {
        $tableConfigurationRepository->getByAttributeCode('100')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('10'), 'code' => '10', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('30'), 'code' => '20']),
                BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('30'), 'code' => '30']),
            ])
        );
        $getAttributes->forCode('100')->shouldBeCalled()->willReturn($this->getTableAttribute('100', false, false));

        // xlsx import files may return numeric codes
        $item = [
            'product' => 11111,
            'attribute' => 100,
            10 => '12',
            20 => 50,
            30 => 0,
        ];
        $this->convert($item)->shouldBe([
            'entity' => '11111',
            'attribute_code' => '100',
            'locale' => null,
            'scope' => null,
            'row_values' => [
                '10' => '12',
                '20' => 50,
                '30' => false,
            ],
        ]);
    }

    function it_denormalizes_a_localizable_table_row(GetAttributes $getAttributes)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'nutrition-fr_FR',
            'ingredient' => 'salt',
            'quantity' => '50',
            'allergenic' => '1',
        ];

        $getAttributes->forCode('nutrition')->willReturn($this->getTableAttribute('nutrition', true, false));

        $this->convert($item)->shouldBeLike([
            'entity' => '11111',
            'attribute_code' => 'nutrition',
            'locale' => 'fr_FR',
            'scope' => null,
            'row_values' => [
                'ingredient' => 'salt',
                'quantity' => '50',
                'allergenic' => true,
            ],
        ]);
    }

    function it_adds_a_warning_when_a_column_is_not_found(GetAttributes $getAttributes, StepExecution $stepExecution)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'nutrition',
            'ingredient' => 'salt',
            'quantity' => '50',
            'unknown' => '12',
            'unknown2' => '42',
        ];

        $getAttributes->forCode('nutrition')->willReturn($this->getTableAttribute('nutrition', false, false));

        $this->shouldThrow(new DataArrayConversionException("The 'unknown, unknown2' columns are unknown"))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_when_attribute_does_not_exist(GetAttributes $getAttributes)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'unknown',
            'ingredient' => 'salt',
            'quantity' => '50',
            'allergenic' => '1',
        ];
        $getAttributes->forCode('unknown')->willReturn(null);

        $this->shouldThrow(new DataArrayConversionException("The 'unknown' attribute is unknown"))
            ->during('convert', [$item]);
    }

    function it_throws_an_exception_when_attribute_is_not_a_table_attribute(GetAttributes $getAttributes)
    {
        $item = [
            'product' => '11111',
            'attribute' => 'a_text',
            'ingredient' => 'salt',
            'quantity' => '50',
            'allergenic' => '1',
        ];

        $getAttributes->forCode('a_text')->willReturn(new Attribute(
            'a_text',
            AttributeTypes::TEXT,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            []
        ));

        $this->shouldThrow(new DataArrayConversionException("The 'a_text' attribute is not a table attribute"))
            ->during('convert', [$item]);
    }

    private function getTableAttribute(string $code, bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute(
            $code,
            AttributeTypes::TABLE,
            [],
            $isLocalizable,
            $isScopable,
            null,
            null,
            null,
            '',
            []
        );
    }
}
