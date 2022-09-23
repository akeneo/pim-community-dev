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

use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleSelectValue;

class SimpleSelectValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_simple_select_value_from_product_value(): void
    {
        $expectedValue = new SimpleSelectValue('blue');
        $productValue = OptionValue::value('simple_select_attribute_code', 'blue');

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_simpleselect';
    }
}
