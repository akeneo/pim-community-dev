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

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NumberValue;

class NumberValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_decimal_number_value_from_product_value(): void
    {
        $expectedValue = new NumberValue('10.4');
        $productValue = ScalarValue::value('number_attribute_code', 10.4);

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    /**
     * @test
     */
    public function it_hydrates_an_integer_number_value_from_product_value(): void
    {
        $expectedValue = new NumberValue('10');
        $productValue = ScalarValue::value('number_attribute_code', 10);

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_number';
    }
}
