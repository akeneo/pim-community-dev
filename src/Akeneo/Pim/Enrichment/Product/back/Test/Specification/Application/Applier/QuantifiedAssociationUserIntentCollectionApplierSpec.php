<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedProduct;
use Akeneo\Pim\Enrichment\Product\Application\Applier\QuantifiedAssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QuantifiedAssociationUserIntentCollectionApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProducts $getViewableProducts,
        GetViewableProductModels $getViewableProductModels
    ) {
        $this->beConstructedWith($productUpdater, $getViewableProducts, $getViewableProductModels);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationUserIntentCollectionApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_associates_quantified_products_by_updating_a_quantity(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('foo', 8)]),
            ]),
            $product,
            10
        );
    }

    function it_associates_quantified_products_by_adding_one_association(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'bar', 'quantity' => 4],
                    ['identifier' => 'baz', 'quantity' => 3],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('foo', 8), new QuantifiedProduct('baz', 3)]),
            ]),
            $product,
            10
        );
    }

    function it_does_nothing_when_product_is_already_associated_with_the_same_quantity(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedProduct('bar', 4)]),
            ]),
            $product,
            10
        );
    }
}
