<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsIndexer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;

class ProductModelDescendantsIndexerSpec extends ObjectBehavior
{
    function let(
        BulkIndexerInterface $productIndexer,
        BulkRemoverInterface $productRemover,
        BulkIndexerInterface $productModelIndexer,
        BulkRemoverInterface $productModelRemover
    ) {
        $this->beConstructedWith($productIndexer, $productRemover,$productModelIndexer, $productModelRemover);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsIndexer::class);
    }

    function it_is_an_indexer()
    {
        $this->shouldImplement(IndexerInterface::class);
        $this->shouldImplement(BulkIndexerInterface::class);
    }

    function it_is_a_index_remover()
    {
        $this->shouldImplement(RemoverInterface::class);
        $this->shouldImplement(BulkRemoverInterface::class);
    }

    function it_indexes_a_product_model_descendants_that_are_variant_products(
        $productIndexer,
        $productModelIndexer,
        ProductModelInterface $productModel,
        ArrayCollection $productChildren,
        ArrayCollection $productModelChildren,
        ProductInterface $childProduct1,
        ProductInterface $childProduct2
    ) {
        $productModel->getProducts()->willReturn($productChildren);
        $productChildren->isEmpty()->willReturn(false);
        $productChildren->first()->willReturn($childProduct1);
        $productChildren->toArray()->willReturn([$childProduct1, $childProduct2]);
        $productIndexer->indexAll([$childProduct1, $childProduct2], [])->shouldBeCalled();

        $productModel->getProductModels()->willReturn($productModelChildren);
        $productModelChildren->isEmpty()->willReturn(true);
        $productModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $this->index($productModel);
    }

    function it_indexes_a_product_model_descendants_that_are_product_models_and_variant_products(
        $productIndexer,
        $productModelIndexer,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $childProductModel,
        ProductInterface $childVariantProduct1,
        ProductInterface $childVariantProduct2,
        ArrayCollection $emptyProductsChildren,
        ArrayCollection $rootProductModelChildren,
        ArrayCollection $emptyChildProductModelChildren,
        ArrayCollection $productVariantsChildren,
        \ArrayIterator $rootProductModelChildrenIterator
    ) {
        // Starting first recursion
        $rootProductModel->getProducts()->willReturn($emptyProductsChildren);

        // First recursion
        $emptyProductsChildren->isEmpty()->willReturn(true);

        // Starting second recursion
        $rootProductModel->getProductModels()->willReturn($rootProductModelChildren);

        // Second recursion - first round
        $rootProductModelChildren->isEmpty()->willReturn(false);
        $rootProductModelChildren->first()->willReturn($childProductModel);

        $rootProductModelChildren->toArray()->willReturn([$childProductModel]);
        $productModelIndexer->indexAll([$childProductModel], [])->shouldBeCalled();

        $rootProductModelChildren->getIterator()->willReturn($rootProductModelChildrenIterator);
        $rootProductModelChildrenIterator->rewind()->shouldBeCalled();
        $rootProductModelChildrenIterator->valid()->willReturn(true, false);
        $rootProductModelChildrenIterator->current()->willReturn($childProductModel);
        $rootProductModelChildrenIterator->next()->shouldBeCalled();

        $childProductModel->getProductModels()->willReturn($emptyChildProductModelChildren);
        $emptyChildProductModelChildren->isEmpty()->willReturn(true);

        // Second recursion - starting the second round
        $childProductModel->getProductModels()->willReturn($emptyChildProductModelChildren);
        $childProductModel->getProducts()->willReturn($productVariantsChildren);

        // Second recursion - second round - empty product models
        $emptyChildProductModelChildren->isEmpty()->willReturn(true);

        // Second recursion - second round - index product variants
        $productVariantsChildren->isEmpty()->willReturn(false);
        $productVariantsChildren->first()->willReturn($childVariantProduct1);
        $productVariantsChildren->toArray()->willReturn([$childVariantProduct1, $childVariantProduct2]);

        $productIndexer->indexAll([$childVariantProduct1, $childVariantProduct2], [])->shouldBeCalled();

        $this->index($rootProductModel);
    }

    function it_does_not_index_non_product_model_objects(
        $productIndexer,
        $productModelIndexer,
        \stdClass $aWrongObject
    ) {
        $productIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)->during('index', [$aWrongObject]);
    }

    function it_bulk_indexes_the_descendants_of_a_list_of_product_models(
        $productIndexer,
        $productModelIndexer,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductInterface $childProduct1,
        ProductInterface $childProduct2,
        ProductInterface $childProduct3,
        ProductInterface $childProduct4,
        ArrayCollection $productChildren1,
        ArrayCollection $productModelChildren1,
        ArrayCollection $productChildren2,
        ArrayCollection $productModelChildren2
    ) {
        $productModel1->getProducts()->willReturn($productChildren1);
        $productChildren1->isEmpty()->willReturn(false);
        $productChildren1->first()->willReturn($childProduct1);
        $productChildren1->toArray()->willReturn([$childProduct1, $childProduct2]);
        $productIndexer->indexAll([$childProduct1, $childProduct2], [])->shouldBeCalled();

        $productModel1->getProductModels()->willReturn($productModelChildren1);
        $productModelChildren1->isEmpty()->willReturn(true);
        $productModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $productModel2->getProducts()->willReturn($productChildren2);
        $productChildren2->isEmpty()->willReturn(false);
        $productChildren2->first()->willReturn($childProduct3);
        $productChildren2->toArray()->willReturn([$childProduct3, $childProduct4]);
        $productIndexer->indexAll([$childProduct3, $childProduct4], [])->shouldBeCalled();

        $productModel2->getProductModels()->willReturn($productModelChildren2);
        $productModelChildren2->isEmpty()->willReturn(true);
        $productModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([$productModel1, $productModel2]);
    }

    function it_indexes_a_product_model_and_its_descendants_products_with_options(
        $productIndexer,
        ProductModelInterface $productModel,
        ProductInterface $productChild,
        ArrayCollection $children,
        \ArrayIterator $childrenIterator
    ) {
        $children->isEmpty()->willReturn(false);
        $children->first()->willReturn($productChild);
        $children->toArray()->willReturn([$productChild]);
        $children->getIterator()->willReturn($childrenIterator);

        $productModel->getProductModels()->willReturn(new ArrayCollection());
        $productModel->getProducts()->willReturn($children);

        $productIndexer->indexAll([$productChild], ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2'])->shouldBeCalled();

        $this->index($productModel, ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2']);
    }

    function it_indexes_a_product_model_and_its_descendants_product_models_with_options(
        $productModelIndexer,
        ProductModelInterface $productModel,
        ProductModelInterface $productModelChild,
        ArrayCollection $children,
        \ArrayIterator $childrenIterator
    ) {
        $children->isEmpty()->willReturn(false);
        $children->first()->willReturn($productModelChild);
        $children->toArray()->willReturn([$productModelChild]);
        $children->getIterator()->willReturn($childrenIterator);

        $productModel->getProductModels()->willReturn($children);
        $productModel->getProducts()->willReturn(new ArrayCollection());

        $productModelIndexer->indexAll([$productModelChild], ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2'])->shouldBeCalled();

        $this->index($productModel, ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2']);
    }

    function it_does_not_bulk_index_non_product_model_objects(
        $productIndexer,
        $productModelIndexer,
        \stdClass $aWrongObject1,
        \stdClass $aWrongObject2
    ) {
        $productIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('indexAll', [[$aWrongObject1, $aWrongObject2]]);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models(
        $productIndexer,
        $productModelIndexer
    ) {
        $productIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $this->indexAll([]);
    }

    function it_removes_the_descendants_of_a_product_model_that_are_variant_products(
        $productRemover,
        $productModelRemover,
        ProductModelInterface $productModel,
        ArrayCollection $productChildren,
        ArrayCollection $productModelChildren,
        ProductInterface $childProduct1,
        ProductInterface $childProduct2
    ) {
        $productModel->getProducts()->willReturn($productChildren);
        $productChildren->isEmpty()->willReturn(false);
        $productChildren->first()->willReturn($childProduct1);
        $productChildren->toArray()->willReturn([$childProduct1, $childProduct2]);
        $productRemover->removeAll([$childProduct1, $childProduct2])->shouldBeCalled();

        $productModel->getProductModels()->willReturn($productModelChildren);
        $productModelChildren->isEmpty()->willReturn(true);
        $productModelRemover->removeAll(Argument::cetera())->shouldNotBeCalled();

        $this->remove($productModel);
    }

    function it_removes_a_product_model_descendants_that_are_product_models_and_variant_products(
        $productRemover,
        $productModelRemover,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $childProductModel,
        ProductInterface $childVariantProduct1,
        ProductInterface $childVariantProduct2,
        ArrayCollection $emptyProductsChildren,
        ArrayCollection $rootProductModelChildren,
        ArrayCollection $emptyChildProductModelChildren,
        ArrayCollection $productVariantsChildren,
        \ArrayIterator $rootProductModelChildrenIterator
    ) {
        // Starting first recursion
        $rootProductModel->getProducts()->willReturn($emptyProductsChildren);

        // First recursion
        $emptyProductsChildren->isEmpty()->willReturn(true);

        // Starting second recursion
        $rootProductModel->getProductModels()->willReturn($rootProductModelChildren);

        // Second recursion - first round
        $rootProductModelChildren->isEmpty()->willReturn(false);
        $rootProductModelChildren->first()->willReturn($childProductModel);

        $rootProductModelChildren->toArray()->willReturn([$childProductModel]);
        $productModelRemover->removeAll([$childProductModel]);

        $rootProductModelChildren->getIterator()->willReturn($rootProductModelChildrenIterator);
        $rootProductModelChildrenIterator->rewind()->shouldBeCalled();
        $rootProductModelChildrenIterator->valid()->willReturn(true, false);
        $rootProductModelChildrenIterator->current()->willReturn($childProductModel);
        $rootProductModelChildrenIterator->next()->shouldBeCalled();

        $childProductModel->getProductModels()->willReturn($emptyChildProductModelChildren);
        $emptyChildProductModelChildren->isEmpty()->willReturn(true);

        // Second recursion - starting the second round
        $childProductModel->getProductModels()->willReturn($emptyChildProductModelChildren);
        $childProductModel->getProducts()->willReturn($productVariantsChildren);

        // Second recursion - second round - empty product models
        $emptyChildProductModelChildren->isEmpty()->willReturn(true);

        // Second recursion - second round - index product variants
        $productVariantsChildren->isEmpty()->willReturn(false);
        $productVariantsChildren->first()->willReturn($childVariantProduct1);
        $productVariantsChildren->toArray()->willReturn([$childVariantProduct1, $childVariantProduct2]);

        $productRemover->removeAll([$childVariantProduct1, $childVariantProduct2])->shouldBeCalled();

        $this->remove($rootProductModel);
    }

    function it_bulk_removes_the_descendants_of_a_list_of_product_models(
        $productRemover,
        $productModelRemover,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductInterface $childProduct1,
        ProductInterface $childProduct2,
        ProductInterface $childProduct3,
        ProductInterface $childProduct4,
        ArrayCollection $productChildren1,
        ArrayCollection $productModelChildren1,
        ArrayCollection $productChildren2,
        ArrayCollection $productModelChildren2
    ) {
        $productModel1->getProducts()->willReturn($productChildren1);
        $productChildren1->isEmpty()->willReturn(false);
        $productChildren1->first()->willReturn($childProduct1);
        $productChildren1->toArray()->willReturn([$childProduct1, $childProduct2]);
        $productRemover->removeAll([$childProduct1, $childProduct2])->shouldBeCalled();

        $productModel1->getProductModels()->willReturn($productModelChildren1);
        $productModelChildren1->isEmpty()->willReturn(true);
        $productModelRemover->removeAll(Argument::cetera())->shouldNotBeCalled();

        $productModel2->getProducts()->willReturn($productChildren2);
        $productChildren2->isEmpty()->willReturn(false);
        $productChildren2->first()->willReturn($childProduct3);
        $productChildren2->toArray()->willReturn([$childProduct3, $childProduct4]);
        $productRemover->removeAll([$childProduct3, $childProduct4])->shouldBeCalled();

        $productModel2->getProductModels()->willReturn($productModelChildren2);
        $productModelChildren2->isEmpty()->willReturn(true);
        $productModelRemover->removeAll(Argument::cetera())->shouldNotBeCalled();

        $this->removeAll([$productModel1, $productModel2]);
    }

    function it_does_not_bulk_remove_non_product_model_objects(
        $productRemover,
        $productModelRemover,
        \stdClass $aWrongObject1,
        \stdClass $aWrongObject2
    ) {
        $productRemover->removeAll(Argument::cetera())->shouldNotBeCalled();
        $productModelRemover->removeAll(Argument::cetera())->shouldNotBeCalled();

        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('removeAll', [[$aWrongObject1, $aWrongObject2]]);
    }

    function it_does_not_bulk_remove_empty_arrays_of_product_models(
        $productRemover,
        $productModelRemover
    ) {
        $productRemover->removeAll(Argument::cetera())->shouldNotBeCalled();
        $productModelRemover->removeAll(Argument::cetera())->shouldNotBeCalled();

        $this->removeAll([]);
    }
}
