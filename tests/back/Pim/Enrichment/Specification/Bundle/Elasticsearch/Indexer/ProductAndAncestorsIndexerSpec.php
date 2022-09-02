<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetAncestorProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ProductAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $this->beConstructedWith(
            $productIndexer,
            $productModelIndexer,
            $getAncestorProductModelCodes
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndAncestorsIndexer::class);
    }

    function it_indexes_simple_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4()];
        $getAncestorProductModelCodes->fromProductUuids($uuids)->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $productIndexer->indexFromProductUuids($uuids, [])->shouldBeCalled();

        $this->indexFromProductUuids($uuids);
    }

    function it_indexes_products_and_their_ancestors(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $getAncestorProductModelCodes->fromProductUuids($uuids)
            ->willReturn(['rootA', 'sub_pm_B', 'root_B']);

        $productModelIndexer->indexFromProductModelCodes(['rootA', 'sub_pm_B', 'root_B'], [])->shouldBeCalled();
        $productIndexer->indexFromProductUuids($uuids, [])->shouldBeCalled();

        $this->indexFromProductUuids($uuids);
    }

    function it_passes_options_to_the_indexer(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $uuid = Uuid::uuid4();
        $options = ['index_refresh' => 'somerefreshoption'];
        $getAncestorProductModelCodes->fromProductUuids([$uuid])->willReturn(['a_product_model']);

        $productModelIndexer->indexFromProductModelCodes(['a_product_model'], $options)->shouldBeCalled();
        $productIndexer->indexFromProductUuids([$uuid], $options)->shouldBeCalled();

        $this->indexFromProductUuids([$uuid], $options);
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
