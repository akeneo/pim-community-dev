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

use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\MeasurementValue;

class MeasurementValueHydratorTest extends AbstractAttributeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_measurement_value_from_product_value(): void
    {
        $expectedValue = new MeasurementValue('10.4', 'GRAM');

        $productValue = MetricValue::value(
            'measurement_attribute_code',
            new Metric('Weight', 'GRAM', 10.4, 'GRAM', 10.4),
        );

        $this->assertHydratedValueEquals($expectedValue, $productValue);
    }

    protected function getAttributeType(): string
    {
        return 'pim_catalog_metric';
    }
}
