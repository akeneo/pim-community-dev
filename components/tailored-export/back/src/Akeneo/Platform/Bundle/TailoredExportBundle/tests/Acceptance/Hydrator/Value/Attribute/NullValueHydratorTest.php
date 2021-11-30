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

use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\NullValue;

class NullValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_null_value_from_null(): void
    {
        $expectedValue = new NullValue();
        $productValue = null;

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    /**
     * @test
     */
    public function it_hydrates_a_null_value_from_null_product_value(): void
    {
        $expectedValue = new NullValue();
        $productValue = ScalarValue::value('an_attribute_code', null);

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return '';
    }
}
