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

use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue as EnrichmentDateValue;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\DateValue;

class DateValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_date_value_from_product_value(): void
    {
        $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', '2021-03-24 16:00:00');

        $expectedValue = new DateValue($datetime);
        $productValue = EnrichmentDateValue::value('date_attribute_code', $datetime);

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_date';
    }
}
