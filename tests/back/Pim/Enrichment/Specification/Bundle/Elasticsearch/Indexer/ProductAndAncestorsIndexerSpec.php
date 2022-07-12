<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetAncestorProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlFindProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ProductAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes,
        Connection $connection
    ) {
        $this->beConstructedWith(
            $productIndexer,
            $productModelIndexer,
            $getAncestorProductModelCodes,
            new SqlFindProductUuids($connection->getWrappedObject())
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndAncestorsIndexer::class);
    }

    function it_indexes_simple_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes,
        Connection $connection
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4()];
        $getAncestorProductModelCodes->fromProductUuids($uuids)->willReturn([]);
        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['simple_product', 'other_product']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'simple_product' => $uuids[0],
                'other_product' => $uuids[1],
            ]);

        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $productIndexer->indexFromProductUuids($uuids, [])->shouldBeCalled();

        $this->indexFromProductIdentifiers(['simple_product', 'other_product']);
    }

    function it_indexes_products_and_their_ancestors(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes,
        Connection $connection
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $productIdentifiers = ['variant_A1', 'variant_A2', 'simple', 'variant_B_1', 'variant_B_2'];
        $getAncestorProductModelCodes->fromProductUuids($uuids)
            ->willReturn(['rootA', 'sub_pm_B', 'root_B']);

        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => $productIdentifiers], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'variant_A1' => $uuids[0],
                'variant_A2' => $uuids[1],
                'simple' => $uuids[2],
                'variant_B_1' => $uuids[3],
                'variant_B_2' => $uuids[4],
            ]);

        $productModelIndexer->indexFromProductModelCodes(['rootA', 'sub_pm_B', 'root_B'], [])->shouldBeCalled();
        $productIndexer->indexFromProductUuids($uuids, [])->shouldBeCalled();

        $this->indexFromProductIdentifiers($productIdentifiers);
    }

    function it_passes_options_to_the_indexer(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes,
        Connection $connection
    ) {
        $uuid = Uuid::uuid4();
        $options = ['index_refresh' => 'somerefreshoption'];
        $getAncestorProductModelCodes->fromProductUuids([$uuid])->willReturn(['a_product_model']);

        $connection
            ->fetchAllKeyValue(Argument::any(), ['identifiers' => ['a_variant']], Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'a_variant' => $uuid,
            ]);

        $productModelIndexer->indexFromProductModelCodes(['a_product_model'], $options)->shouldBeCalled();
        $productIndexer->indexFromProductUuids([$uuid], $options)->shouldBeCalled();

        $this->indexFromProductIdentifiers(['a_variant'], $options);
    }

    function it_deletes_products_from_index_and_reindexes_ancestors(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer
    ) {
        $productIndexer
            ->removeFromProductUuids(['d6d6051c-0c00-49cd-8219-c825c72a456e', '386f0ec8-4e4c-4028-acd7-e1195a13a3b5'])
            ->shouldBeCalled();
        $productModelIndexer
            ->indexFromProductModelCodes(['root_pm', 'sub_pm_1', 'sub_pm_2'])
            ->shouldBeCalled();

        $this->removeFromProductUuidsAndReindexAncestors(
            ['d6d6051c-0c00-49cd-8219-c825c72a456e', '386f0ec8-4e4c-4028-acd7-e1195a13a3b5'],
            ['root_pm', 'sub_pm_1', 'sub_pm_2']
        );
    }
}
