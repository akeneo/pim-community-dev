<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class ProductModelDescendantsAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductUuids $getDescendantVariantProductUuids,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $this->beConstructedWith(
            $productIndexer,
            $productModelIndexer,
            $getDescendantVariantProductUuids,
            $getAncestorAndDescendantProductModelCodes
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsAndAncestorsIndexer::class);
    }

    function it_indexes_the_product_models_with_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductUuids $getDescendantVariantProductUuids,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $getDescendantVariantProductUuids->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn($uuids);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2'])->shouldBeCalled();
        $productIndexer->indexFromProductUuids($uuids)->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_indexes_the_product_models_with_product_models_and_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductUuids $getDescendantVariantProductUuids,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];
        $getDescendantVariantProductUuids->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn($uuids);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['sub_pm1', 'sub_pm2']);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2', 'sub_pm1', 'sub_pm2'])->shouldBeCalled();
        $productIndexer->indexFromProductUuids($uuids)->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductUuids $getDescendantVariantProductUuids,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $productIndexer->indexFromProductUuids(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
        $getDescendantVariantProductUuids->fromProductModelCodes(Argument::cetera())->shouldNotBeCalled();
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
