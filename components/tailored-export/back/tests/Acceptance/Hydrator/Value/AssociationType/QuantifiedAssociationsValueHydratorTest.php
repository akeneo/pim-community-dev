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

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\QuantifiedAssociationsValue;

class QuantifiedAssociationsValueHydratorTest extends AbstractAssociationTypeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_quantified_associations_value_from_empty_product_quantified_associations(): void
    {
        $product = new Product();

        $associationValue = new QuantifiedAssociationsValue([], []);

        $this->assertHydratedValueEquals($associationValue, $product, true);
    }

    /**
     * @test
     */
    public function it_hydrates_a_quantified_associations_value_from_product_quantified_associations(): void
    {
        $product = new Product();

        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized(
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
        ));

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
