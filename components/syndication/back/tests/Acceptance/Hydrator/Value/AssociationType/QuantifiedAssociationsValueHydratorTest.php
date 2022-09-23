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
use Akeneo\Platform\Syndication\Application\Common\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\QuantifiedAssociationsValue;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

class QuantifiedAssociationsValueHydratorTest extends AbstractAssociationTypeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_quantified_associations_value_from_empty_product_quantified_associations(): void
    {
        $product = new ConnectorProduct(
            Uuid::fromString('product_uuid'),
            'bag',
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

        $associationValue = new QuantifiedAssociationsValue([], []);

        $this->assertHydratedValueEquals($associationValue, $product, true);
    }

    /**
     * @test
     */
    public function it_hydrates_a_quantified_associations_value_from_product_quantified_associations(): void
    {
        $product = new ConnectorProduct(
            Uuid::fromString('product_uuid'),
            'bag',
            new DateTimeImmutable('now'),
            new DateTimeImmutable('now'),
            true,
            'family_code',
            [],
            [],
            'parent_product_code',
            [],
            [
                'X_SELL' => [
                    'products' => [
                        [
                            'identifier' => 'bag',
                            'quantity' => 1,
                        ],
                        [
                            'identifier' => 'shoes',
                            'quantity' => 23,
                        ],
                    ],
                    'product_models' => [
                        [
                            'identifier' => 'diana',
                            'quantity' => 2,
                        ],
                        [
                            'identifier' => 'jean',
                            'quantity' => 46,
                        ],
                    ],
                ],
            ],
            [],
            new ReadValueCollection([]),
            null,
            null
        );

        $associationValue = new QuantifiedAssociationsValue(
            [
                new QuantifiedAssociation('bag', 1),
                new QuantifiedAssociation('shoes', 23),
            ],
            [
                new QuantifiedAssociation('diana', 2),
                new QuantifiedAssociation('jean', 46),
            ],
        );

        $this->assertHydratedValueEquals($associationValue, $product, true);
    }
}
