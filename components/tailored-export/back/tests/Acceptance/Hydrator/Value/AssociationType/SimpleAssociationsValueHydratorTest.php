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

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\SimpleAssociationsValue;

class SimpleAssociationsValueHydratorTest extends AbstractAssociationTypeValueHydratorTest
{
    /**
     * @test
     */
    public function it_hydrates_a_simple_associations_value_from_empty_product_associations(): void
    {
        $product = new Product();

        $associationValue = new SimpleAssociationsValue([], [], []);

        $this->assertHydratedValueEquals($associationValue, $product, false);
    }

    /**
     * @test
     */
    public function it_hydrates_a_simple_associations_value_from_product_associations(): void
    {
        $product = new Product();

        $bagProduct = new Product();
        $bagProduct->setIdentifier('bag');
        $shoesProduct = new Product();
        $shoesProduct->setIdentifier('shoes');

        $dianaProductModel = new ProductModel();
        $dianaProductModel->setCode('diana');
        $jeanProductModel = new ProductModel();
        $jeanProductModel->setCode('jean');

        $tShirtGroup = new Group();
        $tShirtGroup->setCode('tshirt');
        $sweaterGroup = new Group();
        $sweaterGroup->setCode('sweater');

        $xSellAssociationType = new AssociationType();
        $xSellAssociationType->setCode('X_SELL');
        $xSellAssociation = new ProductAssociation();
        $xSellAssociation->setAssociationType($xSellAssociationType);

        $product->addAssociation($xSellAssociation);
        $product->addAssociatedProduct($bagProduct, 'X_SELL');
        $product->addAssociatedProduct($shoesProduct, 'X_SELL');
        $product->addAssociatedProductModel($dianaProductModel, 'X_SELL');
        $product->addAssociatedProductModel($jeanProductModel, 'X_SELL');
        $product->addAssociatedGroup($tShirtGroup, 'X_SELL');
        $product->addAssociatedGroup($sweaterGroup, 'X_SELL');

        $associationValue = new SimpleAssociationsValue(
            [
                'bag',
                'shoes',
            ],
            [
                'diana',
                'jean',
            ],
            [
                'tshirt',
                'sweater',
            ],
        );

        $this->assertHydratedValueEquals($associationValue, $product, false);
    }
}
