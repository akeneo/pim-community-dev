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

namespace Akeneo\Platform\Syndication\Test\Acceptance\Hydrator\Value\Property;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationCollection;
use Akeneo\Platform\Syndication\Application\Common\Selection\Code\CodeSelection;
use Akeneo\Platform\Syndication\Application\Common\Source\PropertySource;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\CodeValue;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\NullValue;
use DateTimeImmutable;

class CodeValueHydratorTest extends AbstractPropertyValueHydratorTest
{
    /**
     * @test
     */
    public function it_returns_value_properties_from_product(): void
    {
        $productModel = new ConnectorProductModel(
            12,
            'a_code',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            'parent_product_code',
            'family_code',
            'family_variant_code',
            [],
            [],
            [],
            [],
            new ReadValueCollection([]),
            null
        );

        $valueHydrated = $this->getHydrator()->hydrate(new PropertySource(
            'uuid',
            'code',
            null,
            null,
            OperationCollection::create([]),
            new CodeSelection()
        ), $productModel);
        $this->assertEquals(new CodeValue('a_code'), $valueHydrated);
    }

    public function it_returns_null_value_when_value_is_empty(): void
    {
        $productModel = new ConnectorProductModel(
            12,
            'a_code',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            'parent_product_code',
            'family_code',
            'family_variant_code',
            [],
            [],
            [],
            [],
            new ReadValueCollection([]),
            null
        );

        $this->assertEquals(new NullValue(), $this->getHydrator()->hydrate(new PropertySource(
            'uuid',
            'code',
            null,
            null,
            OperationCollection::create([]),
            new CodeSelection()
        ), $productModel));
    }
}
