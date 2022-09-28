<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\Application\Applier\QuantifiedAssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class QuantifiedAssociationUserIntentCollectionApplierSpec extends ObjectBehavior
{
    const FOO_UUID = '337d8ac9-1afe-4f1b-845c-b124412199da';
    const BAR_UUID = '5dd9eb8b-261f-4e76-bf1d-f407063f931d';
    const BAZ_UUID = 'c8bb12bd-a40a-4ff1-8b01-837a95b0ead2';

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
                    ['uuid' => self::FOO_UUID, 'quantity' => 2],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['uuid' => self::FOO_UUID, 'quantity' => 8],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity(self::FOO_UUID, 8)]),
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
                    ['uuid' => self::FOO_UUID, 'quantity' => 2],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['uuid' => self::FOO_UUID, 'quantity' => 8],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                    ['uuid' => self::BAZ_UUID, 'quantity' => 3],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity(self::FOO_UUID, 8), new QuantifiedEntity(self::BAZ_UUID, 3)]),
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
                    ['uuid' => self::FOO_UUID, 'quantity' => 2],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity(self::BAR_UUID, 4)]),
            ]),
            $product,
            10
        );
    }

    function it_dissociates_quantified_products(ObjectUpdaterInterface $productUpdater)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['uuid' => self::FOO_UUID, 'quantity' => 2],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProducts('bundle', [self::FOO_UUID, self::BAZ_UUID]),
            ]),
            $product,
            10
        );
    }

    function it_dissociates_all_quantified_products(ObjectUpdaterInterface $productUpdater)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['uuid' => self::FOO_UUID, 'quantity' => 2],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProducts('bundle', [self::FOO_UUID, self::BAR_UUID]),
            ]),
            $product,
            10
        );
    }

    function it_replaces_quantified_products(ObjectUpdaterInterface $productUpdater, GetViewableProducts $getViewableProducts)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['uuid' => self::FOO_UUID, 'quantity' => 2],
                    ['uuid' => self::BAR_UUID, 'quantity' => 4],
                    ['uuid' => self::BAZ_UUID, 'quantity' => 5],
                ],
            ],
        ]));

        $getViewableProducts->fromProductUuids([
            Uuid::fromString(self::FOO_UUID),
            Uuid::fromString(self::BAR_UUID),
            Uuid::fromString(self::BAZ_UUID)
        ], 10)->willReturn([self::BAR_UUID, self::FOO_UUID]);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['uuid' => self::FOO_UUID, 'quantity' => 8],
                    ['uuid' => self::BAZ_UUID, 'quantity' => 5],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new ReplaceAssociatedQuantifiedProducts('bundle', [
                    new QuantifiedEntity(self::FOO_UUID, 8),
                ]),
            ]),
            $product,
            10
        );
    }

    function it_associates_quantified_product_models_by_updating_a_quantity(
        ObjectUpdaterInterface $productUpdater,
    ) {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProductModels('bundle', [new QuantifiedEntity('foo', 8)]),
            ]),
            $product,
            10
        );
    }

    function it_dissociates_quantified_product_models(ObjectUpdaterInterface $productUpdater)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProductModels('bundle', ['foo', 'baz']),
            ]),
            $product,
            10
        );
    }

    function it_replaces_quantified_product_models(ObjectUpdaterInterface $productUpdater, GetViewableProductModels $getViewableProductModels)
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]));

        $getViewableProductModels->fromProductModelCodes(['bar', 'baz', 'foo'], 10)->willReturn(['bar', 'foo']);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_models' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new ReplaceAssociatedQuantifiedProductModels('bundle', [
                    new QuantifiedEntity('foo', 8),
                ]),
            ]),
            $product,
            10
        );
    }
}
