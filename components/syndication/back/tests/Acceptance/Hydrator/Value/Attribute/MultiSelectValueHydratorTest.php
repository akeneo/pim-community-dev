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

use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MultiSelectValue;

class MultiSelectValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_multi_select_value_from_product_value(): void
    {
        $expectedValue = new MultiSelectValue(['blue', 'green', 'red']);
        $productValue = OptionsValue::value('multi_select_attribute_code', ['blue', 'green', 'red']);

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_multiselect';
    }
}
