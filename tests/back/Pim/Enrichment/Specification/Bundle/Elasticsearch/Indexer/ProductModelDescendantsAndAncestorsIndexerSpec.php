<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlFindProductUuids;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductIdentifiers;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ProductModelDescendantsAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        Connection $connection
    ) {
        $this->beConstructedWith(
            $productIndexer,
            $productModelIndexer,
            $getDescendantVariantProductIdentifiers,
            $getAncestorAndDescendantProductModelCodes,
            new SqlFindProductUuids($connection->getWrappedObject())
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsAndAncestorsIndexer::class);
    }

    function it_indexes_the_product_models_with_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        Connection $connection
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['foo', 'bar', 'pika', 'chu']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn([]);

        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['foo', 'bar', 'pika', 'chu']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'foo' => $uuids[0],
                'bar' => $uuids[1],
                'pika' => $uuids[2],
                'chu' => $uuids[3],
            ]);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2'])->shouldBeCalled();
        $productIndexer->indexFromProductUuids($uuids)->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_indexes_the_product_models_with_product_models_and_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        Connection $connection
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['foo', 'bar', 'pika', 'chu']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['sub_pm1', 'sub_pm2']);

        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['foo', 'bar', 'pika', 'chu']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'foo' => $uuids[0],
                'bar' => $uuids[1],
                'pika' => $uuids[2],
                'chu' => $uuids[3],
            ]);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2', 'sub_pm1', 'sub_pm2'])->shouldBeCalled();
        $productIndexer->indexFromProductUuids($uuids)->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $productIndexer->indexFromProductUuids(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductModelCodes([]);
    }

    function it_bulk_removes_sub_product_models_and_descendants_from_index_and_index_ancestor(
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getAncestorAndDescendantProductModelCodes->getOnlyAncestorsFromProductModelIds([1, 2])->willReturn([20, 21]);
        $productModelIndexer->indexFromProductModelCodes([20, 21])->shouldBeCalled();

        $productModelIndexer->removeFromProductModelIds([1, 2])->shouldBeCalled();

        $this->removeFromProductModelIds([1, 2]);
    }

    function it_bulk_removes_root_product_models_and_descendants_from_index_and_index_ancestor(
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getAncestorAndDescendantProductModelCodes->getOnlyAncestorsFromProductModelIds([1, 2])->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->removeFromProductModelIds([1, 2])->shouldBeCalled();

        $this->removeFromProductModelIds([1, 2]);
    }

    function it_does_not_bulk_remove_empty_arrays_of_product_models(
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getAncestorAndDescendantProductModelCodes->getOnlyAncestorsFromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();

        $this->removeFromProductModelIds([]);
    }
}
