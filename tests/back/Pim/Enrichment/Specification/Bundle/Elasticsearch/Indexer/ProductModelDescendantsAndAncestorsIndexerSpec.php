<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\GetDescendantVariantProductIdentifiers;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelDescendantsAndAncestorsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $this->beConstructedWith(
            $productIndexer,
            $productModelIndexer,
            $getDescendantVariantProductIdentifiers,
            $getAncestorAndDescendantProductModelCodes
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsAndAncestorsIndexer::class);
    }

    function it_indexes_product_model_with_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm'])
            ->willReturn(['foo', 'bar']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm'])
            ->willReturn([]);

        $productModelIndexer->indexFromProductModelCodes(['pm'], [])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar'], [])->shouldBeCalled();

        $this->indexFromProductModelCode('pm');
    }

    function it_indexes_a_product_model_with_sub_product_models_and_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['root'])
            ->willReturn(['foo', 'bar']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['root'])
            ->willReturn(['code']);

        $productModelIndexer->indexFromProductModelCodes(['root', 'code'], [])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar'], [])->shouldBeCalled();

        $this->indexFromProductModelCode('root');
    }

    function it_bulk_indexes_the_product_models_with_product_models_and_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['foo', 'bar', 'pika', 'chu']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm1', 'pm2'])
            ->willReturn(['sub_pm1', 'sub_pm2']);

        $productModelIndexer->indexFromProductModelCodes(['pm1', 'pm2', 'sub_pm1', 'sub_pm2'], [])->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar', 'pika', 'chu'], [])->shouldBeCalled();

        $this->indexFromProductModelCodes(['pm1', 'pm2']);
    }

    function it_indexes_a_product_model_and_its_descendants_products_with_options(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        GetDescendantVariantProductIdentifiers $getDescendantVariantProductIdentifiers,
        GetAncestorAndDescendantProductModelCodes $getAncestorAndDescendantProductModelCodes
    ) {
        $options = ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2'];
        $getDescendantVariantProductIdentifiers->fromProductModelCodes(['pm'])
            ->willReturn(['foo']);

        $getAncestorAndDescendantProductModelCodes->fromProductModelCodes(['pm'])
            ->willReturn(['code']);

        $productModelIndexer->indexFromProductModelCodes(['pm', 'code'], $options)->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo'], $options)->shouldBeCalled();

        $this->indexFromProductModelCode('pm', $options);
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
}
