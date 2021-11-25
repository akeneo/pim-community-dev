<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\StringValue;

class TableValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_string_value_from_product_value(): void
    {
        $expectedValue = new StringValue('[{"identifier":"my value"}]');
        $productValue = TableValue::value(
            'table_attribute_code',
            Table::fromNormalized([['identifier_48bb84cd-3b3d-4b61-bee3-b8743448ef7f' => 'my value']]),
        );

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_table';
    }
}
