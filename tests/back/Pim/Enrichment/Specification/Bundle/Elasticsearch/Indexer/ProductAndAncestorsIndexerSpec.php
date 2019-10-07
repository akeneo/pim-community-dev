<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetAncestorProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $this->beConstructedWith($productIndexer, $productModelIndexer, $getAncestorProductModelCodes);
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
        $getAncestorProductModelCodes->fromProductIdentifiers(['simple_product', 'other_product'])->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $productIndexer->indexFromProductIdentifiers(['simple_product', 'other_product'], [])->shouldBeCalled();

        $this->indexFromProductIdentifiers(['simple_product', 'other_product']);
    }

    function it_indexes_products_and_their_ancestors(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $productIdentifiers = ['variant_A1', 'variant_A2', 'simple', 'variant_B_1', 'variant_B_2'];
        $getAncestorProductModelCodes->fromProductIdentifiers($productIdentifiers)
            ->willReturn(['rootA', 'sub_pm_B', 'root_B']);

        $productModelIndexer->indexFromProductModelCodes(['rootA', 'sub_pm_B', 'root_B'], [])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers($productIdentifiers, [])->shouldBeCalled();

        $this->indexFromProductIdentifiers($productIdentifiers);
    }

    function it_passes_options_to_the_indexer(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorProductModelCodes $getAncestorProductModelCodes
    ) {
        $options = ['index_refresh' => 'somerefreshoption'];
        $getAncestorProductModelCodes->fromProductIdentifiers(['a_variant'])->willReturn(['a_product_model']);

        $productModelIndexer->indexFromProductModelCodes(['a_product_model'], $options)->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['a_variant'], $options)->shouldBeCalled();

        $this->indexFromProductIdentifiers(['a_variant'], $options);
    }

    function it_deletes_products_from_index_and_reindexes_ancestors(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer
    ) {
        $productIndexer->removeFromProductIds([44, 56])->shouldBeCalled();
        $productModelIndexer->indexFromProductModelCodes(['root_pm', 'sub_pm_1', 'sub_pm_2'])->shouldBeCalled();

        $this->removeFromProductIdsAndReindexAncestors([44, 56], ['root_pm', 'sub_pm_1', 'sub_pm_2']);
    }
}
