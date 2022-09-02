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
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\BooleanValue;

class BooleanValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_boolean_value_from_product_value(): void
    {
        $this->assertHydratedValueEquals(
            new BooleanValue(true),
            ScalarValue::value('boolean_attribute_code', true),
        );

        $this->assertHydratedValueEquals(
            new BooleanValue(false),
            ScalarValue::value('boolean_attribute_code', false),
        );
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_boolean';
    }
}
