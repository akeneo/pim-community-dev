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

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Value\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Completeness\MaskItemGenerator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;

class MaskItemGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldImplement(MaskItemGeneratorForAttributeType::class);
        $this->shouldHaveType(MaskItemGenerator::class);
    }

    function it_supports_table_attributes()
    {
        $this->supportedAttributeTypes()->shouldBe(['pim_catalog_table']);
    }

    function it_builds_a_mask_for_an_empty_value()
    {
        $this->forRawValue('nutrition', 'mobile', 'en_US', [])->shouldBe([]);
    }

    function it_builds_a_mask_for_a_table_value_when_only_first_column_is_entirely_filled()
    {
        $table = [
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::quantity() => 12],
            [ColumnIdGenerator::ingredient() => 'sugar', ColumnIdGenerator::quantity() => 8],
            [ColumnIdGenerator::ingredient() => 'egg', ColumnIdGenerator::isAllergenic() => true],
            [ColumnIdGenerator::ingredient() => 'pepper', ColumnIdGenerator::quantity() => 12],
        ];
        $this->forRawValue('nutrition', 'mobile', 'en_US', $table)->shouldBe([
            'nutrition-' . ColumnIdGenerator::ingredient() . '-mobile-en_US',
        ]);
    }

    function it_builds_a_mask_for_a_table_value_when_three_columns_are_entirely_filled()
    {
        $table = [
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::quantity() => 12, ColumnIdGenerator::isAllergenic() => true],
            [ColumnIdGenerator::ingredient() => 'sugar', ColumnIdGenerator::quantity() => 8, ColumnIdGenerator::isAllergenic() => true],
            [ColumnIdGenerator::ingredient() => 'egg', ColumnIdGenerator::quantity() => 3, ColumnIdGenerator::isAllergenic() => true],
            [ColumnIdGenerator::ingredient() => 'pepper', ColumnIdGenerator::quantity() => 12, ColumnIdGenerator::isAllergenic() => true],
        ];
        $this->forRawValue('nutrition', 'mobile', 'en_US', $table)->shouldBe([
            'nutrition-' . ColumnIdGenerator::ingredient() . '-mobile-en_US',
            'nutrition-' . ColumnIdGenerator::isAllergenic() . '-mobile-en_US',
            'nutrition-' . ColumnIdGenerator::ingredient() . '-' . ColumnIdGenerator::isAllergenic() . '-mobile-en_US',
            'nutrition-' . ColumnIdGenerator::quantity() . '-mobile-en_US',
            'nutrition-' . ColumnIdGenerator::ingredient() . '-' . ColumnIdGenerator::quantity() . '-mobile-en_US',
            'nutrition-' . ColumnIdGenerator::isAllergenic() . '-' . ColumnIdGenerator::quantity() . '-mobile-en_US',
            'nutrition-' . ColumnIdGenerator::ingredient() . '-' . ColumnIdGenerator::isAllergenic() . '-' . ColumnIdGenerator::quantity() . '-mobile-en_US',
        ]);
    }
}
