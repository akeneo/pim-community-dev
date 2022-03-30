<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationUserIntentCollectionApplierSpec extends ObjectBehavior
{
    function let(ObjectUpdaterInterface $productUpdater, GetViewableProducts $getViewableProducts)
    {
        $this->beConstructedWith($productUpdater, $getViewableProducts);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationUserIntentCollectionApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    function it_supports_association_user_intent_collection()
    {
        $this->getSupportedUserIntents()->shouldReturn([AssociationUserIntentCollection::class]);
    }

    function it_applies_associate_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProduct])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateProducts('X_SELL', ['foo', 'bar']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['baz', 'foo', 'bar'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_applies_multiple_associate_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProduct])
        );
        $product->getAssociatedProducts('UPSELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProduct])
        );

        $collection = new AssociationUserIntentCollection([
            new AssociateProducts('X_SELL', ['foo', 'bar']),
            new AssociateProducts('UPSELL', ['foo', 'bar']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['baz', 'foo', 'bar'],
            ],
            'UPSELL' => [
                'products' => ['baz', 'foo', 'bar'],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_applies_multiple_same_associate_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $product->getAssociatedProducts('X_SELL')
            ->shouldBeCalledOnce()
            ->willReturn(new ArrayCollection([$associatedProduct]));

        $collection = new AssociationUserIntentCollection([
            new AssociateProducts('X_SELL', ['foo', 'bar']),
            new AssociateProducts('X_SELL', ['foo', 'toto']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['baz', 'foo', 'bar', 'toto']
            ],
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_products_are_already_associated(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProduct])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateProducts('X_SELL', ['baz']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply($collection, $product, 42);
    }

    function it_applies_dissociate_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('qux');
        $associatedProducts[] = $associatedProduct;

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProducts)
        );
        $collection = new AssociationUserIntentCollection([
            new DissociateProducts('X_SELL', ['baz']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['qux'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_product_to_dissociate_is_not_associated(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProduct])
        );
        $collection = new AssociationUserIntentCollection([
            new DissociateProducts('X_SELL', ['qux']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $this->apply($collection, $product, 42);
    }

    function it_associates_and_dissociates_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProduct])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateProducts('X_SELL', ['qux']),
            new DissociateProducts('X_SELL', ['baz'])
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['qux'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_replaces_associated_products(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProducts $getViewableProducts,
        ProductInterface $product,
    ) {
        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('non_viewable_product');
        $associatedProducts[] = $associatedProduct;

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProducts)
        );
        $collection = new AssociationUserIntentCollection([
            new ReplaceAssociatedProducts('X_SELL', ['quux', 'quuz', 'corge']),
        ]);

        // product is updated with new values and non viewable product identifiers
        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['non_viewable_product', 'quux', 'quuz', 'corge'],
            ]
        ]])->shouldBeCalledOnce();

        $getViewableProducts->fromProductIdentifiers(['baz', 'non_viewable_product'], 42)
            ->shouldBeCalled()
            ->willReturn(['baz']);

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_products_to_associate_are_the_same_as_existing_associated_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('qux');
        $associatedProducts[] = $associatedProduct;

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProducts)
        );
        $collection = new AssociationUserIntentCollection([
            new ReplaceAssociatedProducts('X_SELL', ['qux', 'baz']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $this->apply($collection, $product, 42);
    }

    function it_applies_associate_product_models(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProductModel = new ProductModel();
        $associatedProductModel->setCode('foo');

        $product->getAssociatedProductModels('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProductModel])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateProductModels('X_SELL', ['bar', 'baz']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'product_models' => ['foo', 'bar', 'baz'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_product_models_are_already_associated(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProductModel = new ProductModel();
        $associatedProductModel->setCode('foo');

        $product->getAssociatedProductModels('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProductModel])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateProductModels('X_SELL', ['foo']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply($collection, $product, 42);
    }
}
