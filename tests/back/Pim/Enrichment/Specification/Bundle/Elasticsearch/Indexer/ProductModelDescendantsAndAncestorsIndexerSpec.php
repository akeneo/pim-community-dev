<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantProductModelIds;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIdentifiers;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIds;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelDescendantsAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        GetDescendantVariantProductIds $getDescendantVariantProductIds,
        GetDescendantProductModelIds $getDescendantProductModelIds
    ) {
        $this->beConstructedWith(
            $productIndexer,
            $productModelIndexer,
            $getDescendantVariantProductIdentifiers,
            $getAncestorAndDescendantProductModelCodes,
            $getDescendantVariantProductIds,
            $getDescendantProductModelIds
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
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['foo', 'bar', 'pika', 'chu']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2'])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar', 'pika', 'chu'])->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_indexes_the_product_models_with_product_models_and_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['foo', 'bar', 'pika', 'chu']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['sub_pm1', 'sub_pm2']);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2', 'sub_pm1', 'sub_pm2'])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar', 'pika', 'chu'])->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $productIndexer->indexFromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->indexFromProductModelCodes([]);
    }

    function it_bulk_removes_sub_product_models_and_descendants_from_index_and_index_ancestor(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        GetDescendantVariantProductIds $getDescendantVariantProductIds,
        GetDescendantProductModelIds $getDescendantProductModelIds
    ) {
        $getDescendantVariantProductIds->fromProductModelIds([1, 2])->willReturn([11, 12]);
        $productIndexer->removeFromProductIds([11, 12])->shouldBeCalled();

        $getDescendantProductModelIds->fromProductModelIds([1, 2])->willReturn([]);

        $getAncestorAndDescendantProductModelCodes->getOnlyAncestorsFromProductModelIds([1, 2])->willReturn([20, 21]);
        $productModelIndexer->indexFromProductModelCodes([20, 21])->shouldBeCalled();

        $productModelIndexer->removeFromProductModelIds([1, 2])->shouldBeCalled();

        $this->removeFromProductModelIds([1, 2]);
    }

    function it_bulk_removes_root_product_models_and_descendants_from_index_and_index_ancestor(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        GetDescendantVariantProductIds $getDescendantVariantProductIds,
        GetDescendantProductModelIds $getDescendantProductModelIds
    ) {
        $getDescendantVariantProductIds->fromProductModelIds([1, 2])->willReturn([11, 12]);
        $productIndexer->removeFromProductIds([11, 12])->shouldBeCalled();

        $getDescendantProductModelIds->fromProductModelIds([1, 2])->willReturn([20, 21]);
        $getAncestorAndDescendantProductModelCodes->getOnlyAncestorsFromProductModelIds([1, 2])->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->removeFromProductModelIds([1, 2, 20, 21])->shouldBeCalled();

        $this->removeFromProductModelIds([1, 2]);
    }

    function it_does_not_bulk_remove_empty_arrays_of_product_models(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes,
        GetDescendantVariantProductIds $getDescendantVariantProductIds,
        GetDescendantProductModelIds $getDescendantProductModelIds
    ) {
        $getDescendantVariantProductIds->fromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $productIndexer->removeFromProductIds(Argument::cetera())->shouldNotBeCalled();
        $getDescendantProductModelIds->fromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $getAncestorAndDescendantProductModelCodes->getOnlyAncestorsFromProductModelIds(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->removeFromProductModelIds(Argument::cetera())->shouldNotBeCalled();

        $this->removeFromProductModelIds([]);
    }
}
