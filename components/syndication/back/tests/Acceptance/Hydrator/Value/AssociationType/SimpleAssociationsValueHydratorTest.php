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

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SimpleAssociationsValue;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class SimpleAssociationsValueHydratorTest extends AbstractAssociationTypeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_simple_associations_value_from_empty_product_associations(): void
    {
        $product = new ConnectorProduct(
            Uuid::fromString('product_uuid'),
            'product_code',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            true,
            'family_code',
            [],
            [],
            'parent_product_code',
            [],
            [],
            [],
            new ReadValueCollection([]),
            null,
            null
        );

        $associationValue = new SimpleAssociationsValue([], [], []);

        $this->assertHydratedValueEquals($associationValue, $product, false);
    }
}
