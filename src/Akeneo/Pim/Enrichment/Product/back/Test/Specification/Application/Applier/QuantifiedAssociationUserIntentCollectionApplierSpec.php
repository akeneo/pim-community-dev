<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedProductWithUuid;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\Application\Applier\QuantifiedAssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class QuantifiedAssociationUserIntentCollectionApplierSpec extends ObjectBehavior
{
    function let(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProducts $getViewableProducts,
        GetViewableProductModels $getViewableProductModels,
        Connection $connection
    ) {
        $this->beConstructedWith($productUpdater, $getViewableProducts, $getViewableProductModels, $connection);
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
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('foo', 8)]),
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
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('foo', 8), new QuantifiedEntity('baz', 3)]),
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
                new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('bar', 4)]),
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
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProducts('bundle', ['foo', 'baz']),
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
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
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
                new DissociateQuantifiedProducts('bundle', ['foo', 'bar']),
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
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]));

        $getViewableProducts->fromProductIdentifiers(['bar', 'baz', 'foo'], 10)->willReturn(['bar', 'foo']);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 8],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new ReplaceAssociatedQuantifiedProducts('bundle', [
                    new QuantifiedEntity('foo', 8),
                ]),
            ]),
            $product,
            10
        );
    }

    function it_associates_quantified_products_with_uuids(
        ObjectUpdaterInterface $productUpdater,
        Connection $connection,
    ) {
        $formerAssociatedProduct = new Product();
        $formerAssociatedProduct->setIdentifier('former_association');
        $product = new Product();
        $associatedProduct1 = new Product();
        $associatedProduct2 = new Product();

        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'former_association', 'quantity' => 2],
                ],
            ],
        ]));

        $connection->fetchAllAssociative(Argument::cetera())->shouldBeCalledOnce()
            ->willReturn([['identifier' => 'former_association', 'uuid' => $formerAssociatedProduct->getUuid()->toString()]]);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_uuids' => [
                    ['uuid' => $formerAssociatedProduct->getUuid(), 'quantity' => 2],
                    ['uuid' => $associatedProduct1->getUuid(), 'quantity' => 8],
                    ['uuid' => $associatedProduct2->getUuid(), 'quantity' => 3],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new AssociateQuantifiedProductUuids('bundle', [
                    new QuantifiedProductWithUuid($associatedProduct1->getUuid(), 8),
                    new QuantifiedProductWithUuid($associatedProduct2->getUuid(), 3)
                ]),
            ]),
            $product,
            10
        );
    }

    function it_dissociates_quantified_products_with_uuids(
        ObjectUpdaterInterface $productUpdater,
        Connection $connection
    ) {
        $product = new Product();
        $formerAssociatedProduct1 = new Product();
        $formerAssociatedProduct1->setIdentifier('foo');
        $formerAssociatedProduct2 = new Product();
        $formerAssociatedProduct2->setIdentifier('bar');
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                ],
            ],
        ]));

        $connection->fetchAllAssociative(Argument::cetera())->shouldBeCalledOnce()
            ->willReturn([
                ['identifier' => 'foo', 'uuid' => $formerAssociatedProduct1->getUuid()->toString()],
                ['identifier' => 'bar', 'uuid' => $formerAssociatedProduct2->getUuid()->toString()],
            ]);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_uuids' => [
                    ['uuid' => $formerAssociatedProduct2->getUuid()->toString(), 'quantity' => 4],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new DissociateQuantifiedProductUuids('bundle', [$formerAssociatedProduct1->getUuid(), Uuid::uuid4()]),
            ]),
            $product,
            10
        );
    }

    function it_replaces_quantified_products_with_uuids(
        ObjectUpdaterInterface $productUpdater,
        GetViewableProducts $getViewableProducts,
        Connection $connection
    ) {
        $product = new Product();
        $formerAssociatedProduct1 = new Product();
        $formerAssociatedProduct1->setIdentifier('foo');
        $formerAssociatedProduct2 = new Product();
        $formerAssociatedProduct2->setIdentifier('bar');
        $formerAssociatedProduct3 = new Product();
        $formerAssociatedProduct3->setIdentifier('baz');
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
            'bundle' => [
                'products' => [
                    ['identifier' => 'foo', 'quantity' => 2],
                    ['identifier' => 'bar', 'quantity' => 4],
                    ['identifier' => 'baz', 'quantity' => 5],
                ],
            ],
        ]));

        $getViewableProducts->fromProductUuids([
            $formerAssociatedProduct1->getUuid()->toString(),
            $formerAssociatedProduct2->getUuid()->toString(),
            $formerAssociatedProduct3->getUuid()->toString(),
        ], 10)->willReturn([
            $formerAssociatedProduct2->getUuid()->toString(),
            $formerAssociatedProduct1->getUuid()->toString(),
        ]);

        $connection->fetchAllAssociative(Argument::cetera())->shouldBeCalledOnce()
            ->willReturn([
                ['identifier' => 'foo', 'uuid' => $formerAssociatedProduct1->getUuid()->toString()],
                ['identifier' => 'bar', 'uuid' => $formerAssociatedProduct2->getUuid()->toString()],
                ['identifier' => 'baz', 'uuid' => $formerAssociatedProduct3->getUuid()->toString()],
            ]);

        $productUpdater->update($product, ['quantified_associations' => [
            'bundle' => [
                'product_uuids' => [
                    ['uuid' => $formerAssociatedProduct1->getUuid()->toString(), 'quantity' => 8],
                    ['uuid' => $formerAssociatedProduct3->getUuid()->toString(), 'quantity' => 5],
                ],
            ],
        ]])->shouldBeCalledOnce();

        $this->apply(
            new QuantifiedAssociationUserIntentCollection([
                new ReplaceAssociatedQuantifiedProductUuids('bundle', [
                    new QuantifiedProductWithUuid($formerAssociatedProduct1->getUuid(), 8),
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
