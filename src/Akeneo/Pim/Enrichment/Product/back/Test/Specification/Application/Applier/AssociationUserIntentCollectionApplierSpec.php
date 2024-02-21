<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductUuids;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationUserIntentCollectionApplierSpec extends ObjectBehavior
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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));

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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->getAssociatedProducts('X_SELL')
            ->shouldBeCalledTimes(2)
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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));

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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'qux'));
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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));

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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledTimes(2)->willReturn(
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
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'non_viewable_product'));
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

    function it_replaces_associated_products_with_uuids(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProducts $getViewableProducts,
        ProductInterface $product,
    ) {
        $viewableProduct = new Product();
        $nonViewableProduct = new Product();
        $associatedProducts = [$viewableProduct, $nonViewableProduct];

        $product->getAssociatedProducts('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProducts)
        );
        $newAssociatedProductUuid1 = Uuid::uuid4()->toString();
        $newAssociatedProductUuid2 = Uuid::uuid4()->toString();
        $newAssociatedProductUuid3 = Uuid::uuid4()->toString();
        $collection = new AssociationUserIntentCollection([
            new ReplaceAssociatedProductUuids('X_SELL', [$newAssociatedProductUuid1, $newAssociatedProductUuid2, $newAssociatedProductUuid3]),
        ]);

        // product is updated with new values and non viewable product identifiers
        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'product_uuids' => [$nonViewableProduct->getUuid()->toString(), $newAssociatedProductUuid1, $newAssociatedProductUuid2, $newAssociatedProductUuid3],
            ]
        ]])->shouldBeCalledOnce();

        $uuids = [$viewableProduct->getUuid()->toString(), $nonViewableProduct->getUuid()->toString()];
        \sort($uuids);
        $uuids = array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids);

        $getViewableProducts->fromProductUuids($uuids, 42)
            ->willReturn([$viewableProduct->getUuid()]);

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_products_to_associate_are_the_same_as_existing_associated_products(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'qux'));
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

    function it_applies_dissociate_product_models(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
    ) {
        $associatedProductModel1 = new ProductModel();
        $associatedProductModel1->setCode('foo');
        $associatedProductModel2 = new ProductModel();
        $associatedProductModel2->setCode('bar');
        $associatedProductModels = [$associatedProductModel1, $associatedProductModel2];

        $product->getAssociatedProductModels('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProductModels)
        );
        $collection = new AssociationUserIntentCollection([
            new DissociateProductModels('X_SELL', ['foo']),
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'product_models' => ['bar'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_product_model_to_dissociate_is_not_associated(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product
    ) {
        $associatedProductModel = new ProductModel();
        $associatedProductModel->setCode('baz');

        $product->getAssociatedProductModels('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$associatedProductModel])
        );
        $collection = new AssociationUserIntentCollection([
            new DissociateProductModels('X_SELL', ['not_associated_model_code']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $this->apply($collection, $product, 42);
    }

    function it_replaces_associated_product_models(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProductModels $getViewableProductModels,
        ProductInterface $product,
    ) {
        $associatedProductModel1 = new ProductModel();
        $associatedProductModel1->setCode('viewable_product_model');
        $associatedProductModel2 = new ProductModel();
        $associatedProductModel2->setCode('non_viewable_product_model');
        $associatedProductModels = [$associatedProductModel1, $associatedProductModel2];

        $product->getAssociatedProductModels('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProductModels)
        );
        $collection = new AssociationUserIntentCollection([
            new ReplaceAssociatedProductModels('X_SELL', ['quux', 'quuz', 'corge']),
        ]);

        $getViewableProductModels->fromProductModelCodes(['non_viewable_product_model', 'viewable_product_model'], 42)
            ->shouldBeCalledOnce()
            ->willReturn(['viewable_product_model']);
        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'product_models' => ['non_viewable_product_model', 'quux', 'quuz', 'corge'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_product_models_to_associate_are_the_same_as_existing_associated_product_models(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProductModels $getViewableProductModels,
        ProductInterface $product,
    ) {
        $associatedProductModel1 = new ProductModel();
        $associatedProductModel1->setCode('foo');
        $associatedProductModel2 = new ProductModel();
        $associatedProductModel2->setCode('bar');
        $associatedProductModels = [$associatedProductModel1, $associatedProductModel2];

        $product->getAssociatedProductModels('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection($associatedProductModels)
        );
        $collection = new AssociationUserIntentCollection([
            new ReplaceAssociatedProductModels('X_SELL', ['foo', 'bar']),
        ]);

        $getViewableProductModels->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();
        $this->apply($collection, $product, 42);
    }

    function it_applies_associate_groups(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product,
        GroupInterface $group,
    ) {
        $group->getCode()->willReturn('group1');

        $product->getAssociatedGroups('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$group->getWrappedObject()])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateGroups('X_SELL', ['group2', 'group3'])
        ]);

        $productUpdater->update($product, ['associations' => [
            'X_SELL' => [
                'groups' => ['group1', 'group2', 'group3'],
            ]
        ]])->shouldBeCalledOnce();

        $this->apply($collection, $product, 42);
    }

    function it_does_nothing_if_groups_are_already_associated(
        ObjectUpdaterInterface $productUpdater,
        ProductInterface $product
    ) {
        $group = new Group();
        $group->setCode('group1');

        $product->getAssociatedGroups('X_SELL')->shouldBeCalledOnce()->willReturn(
            new ArrayCollection([$group])
        );
        $collection = new AssociationUserIntentCollection([
            new AssociateGroups('X_SELL', ['group1']),
        ]);

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply($collection, $product, 42);
    }
}
