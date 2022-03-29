<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
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
    function let(ObjectUpdaterInterface $productUpdater)
    {
        $this->beConstructedWith($productUpdater);
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

    function it_applies_add_associated_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
            new AddAssociatedProducts('X_SELL', ['foo', 'bar']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'products' => ['baz', 'foo', 'bar'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_applies_multiple_add_associated_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(new ArrayCollection([$associatedProduct]));
        $product->getAssociatedProducts('UPSELL')->shouldBeCalledOnce()->willReturn(new ArrayCollection([$associatedProduct]));

        $collection = new AssociationUserIntentCollection([
            new AddAssociatedProducts('X_SELL', ['foo', 'bar']),
            new AddAssociatedProducts('UPSELL', ['foo', 'bar']),
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

    function it_applies_multiple_same_add_associated_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProduct = new Product();
        $associatedProduct->setIdentifier('baz');
        $product->getAssociatedProducts('X_SELL')
            ->shouldBeCalledOnce()
            ->willReturn(new ArrayCollection([$associatedProduct]));

        $collection = new AssociationUserIntentCollection([
            new AddAssociatedProducts('X_SELL', ['foo', 'bar']),
            new AddAssociatedProducts('X_SELL', ['foo', 'toto']),
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

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
            new AddAssociatedProducts('X_SELL', ['baz']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply($collection, $product, 42);
    }
}
