<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductModelIndexerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsIndexer;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;

class ProductModelDescendantsIndexerSpec extends ObjectBehavior
{
    function let(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $this->beConstructedWith($productIndexer, $productModelIndexer, $productModelRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsIndexer::class);
    }

    function it_indexes_a_product_model_descendants_that_are_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository,
        ProductModelInterface $productModel,
        ArrayCollection $productChildren,
        \ArrayIterator $productChildrenIterator,
        ArrayCollection $productModelChildren,
        ProductInterface $childProduct1,
        ProductInterface $childProduct2
    ) {
        $childProduct1->getIdentifier()->willReturn('foo');
        $childProduct2->getIdentifier()->willReturn('bar');

        $productModel->getProducts()->willReturn($productChildren);
        $productChildren->isEmpty()->willReturn(false);
        $productChildren->first()->willReturn($childProduct1);

        $productChildren->getIterator()->willReturn($productChildrenIterator);
        $productChildrenIterator->valid()->willReturn(true, true, false);
        $productChildrenIterator->current()->willReturn($childProduct1, $childProduct2);
        $productChildrenIterator->rewind()->shouldBeCalled();
        $productChildrenIterator->next()->shouldBeCalled();

        $productIndexer->indexFromProductIdentifiers(['foo', 'bar'], [])->shouldBeCalled();

        $productModel->getProductModels()->willReturn($productModelChildren);
        $productModelChildren->isEmpty()->willReturn(true);
        $productModelIndexer->indexFromProductModelCode(Argument::cetera())->shouldNotBeCalled();

        $productModelRepository->findOneByIdentifier('pm')->willReturn($productModel);

        $this->fromProductModelCode('pm');
    }

    function it_indexes_a_product_model_descendants_that_are_product_models_and_variant_products(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $childProductModel,
        ProductInterface $childVariantProduct1,
        ProductInterface $childVariantProduct2,
        ArrayCollection $emptyProductsChildren,
        ArrayCollection $rootProductModelChildren,
        ArrayCollection $emptyChildProductModelChildren,
        ArrayCollection $productVariantsChildren,
        \ArrayIterator $rootProductModelChildrenIterator,
        \ArrayIterator $productVariantsChildrenIterator
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
        $childProductModel->getCode()->willReturn('code');
        $productModelIndexer->indexFromProductModelCodes(['code'], [])->shouldBeCalled();

        $rootProductModelChildren->getIterator()->willReturn($rootProductModelChildrenIterator);
        $rootProductModelChildrenIterator->rewind()->shouldBeCalled();
        $rootProductModelChildrenIterator->valid()->willReturn(true, false, true, false);
        $rootProductModelChildrenIterator->current()->willReturn($childProductModel, $childProductModel);
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

        $productVariantsChildren->getIterator()->willReturn($productVariantsChildrenIterator);
        $productVariantsChildrenIterator->valid()->willReturn(true, true, false);
        $productVariantsChildrenIterator->current()->willReturn($childVariantProduct1, $childVariantProduct2);
        $productVariantsChildrenIterator->rewind()->shouldBeCalled();
        $productVariantsChildrenIterator->next()->shouldBeCalled();

        $childVariantProduct1->getIdentifier()->willReturn('foo');
        $childVariantProduct2->getIdentifier()->willReturn('bar');
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar'], [])->shouldBeCalled();

        $productModelRepository->findOneByIdentifier('root')->willReturn($rootProductModel);

        $this->fromProductModelCode('root');
    }

    function it_bulk_indexes_the_descendants_of_a_list_of_product_models(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductInterface $childProduct1,
        ProductInterface $childProduct2,
        ProductInterface $childProduct3,
        ProductInterface $childProduct4,
        ArrayCollection $productChildren1,
        ArrayCollection $productModelChildren1,
        ArrayCollection $productChildren2,
        ArrayCollection $productModelChildren2,
        \ArrayIterator $productChildrenIterator1,
        \ArrayIterator $productChildrenIterator2
    ) {
        $productModel1->getProducts()->willReturn($productChildren1);
        $productChildren1->isEmpty()->willReturn(false);
        $productChildren1->first()->willReturn($childProduct1);
        $productChildren1->toArray()->willReturn([$childProduct1, $childProduct2]);
        $childProduct1->getIdentifier()->willReturn('foo');
        $childProduct2->getIdentifier()->willReturn('bar');

        $productChildren1->getIterator()->willReturn($productChildrenIterator1);
        $productChildrenIterator1->valid()->willReturn(true, true, false);
        $productChildrenIterator1->current()->willReturn($childProduct1, $childProduct2);
        $productChildrenIterator1->rewind()->shouldBeCalled();
        $productChildrenIterator1->next()->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['foo', 'bar'], [])->shouldBeCalled();

        $productModel1->getProductModels()->willReturn($productModelChildren1);
        $productModelChildren1->isEmpty()->willReturn(true);
        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $productModel2->getProducts()->willReturn($productChildren2);
        $productChildren2->isEmpty()->willReturn(false);
        $productChildren2->first()->willReturn($childProduct3);
        $productChildren2->toArray()->willReturn([$childProduct3, $childProduct4]);
        $childProduct3->getIdentifier()->willReturn('pika');
        $childProduct4->getIdentifier()->willReturn('chu');
        $productChildren2->getIterator()->willReturn($productChildrenIterator2);
        $productChildrenIterator2->valid()->willReturn(true, true, false);
        $productChildrenIterator2->current()->willReturn($childProduct3, $childProduct4);
        $productChildrenIterator2->rewind()->shouldBeCalled();
        $productChildrenIterator2->next()->shouldBeCalled();
        $productIndexer->indexFromProductIdentifiers(['pika', 'chu'], [])->shouldBeCalled();

        $productModel2->getProductModels()->willReturn($productModelChildren2);
        $productModelChildren2->isEmpty()->willReturn(true);
        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $productModelRepository->findOneByIdentifier('pm1')->willReturn($productModel1);
        $productModelRepository->findOneByIdentifier('pm2')->willReturn($productModel2);

        $this->fromProductModelCodes(['pm1', 'pm2']);
    }

    function it_indexes_a_product_model_and_its_descendants_products_with_options(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository,
        ProductModelInterface $productModel,
        ProductInterface $productChild,
        ArrayCollection $children,
        \ArrayIterator $childrenIterator
    ) {
        $children->isEmpty()->willReturn(false);
        $children->first()->willReturn($productChild);
        $children->toArray()->willReturn([$productChild]);
        $children->getIterator()->willReturn($childrenIterator);
        $childrenIterator->valid()->willReturn(true, false);
        $childrenIterator->current()->willReturn($productChild);
        $childrenIterator->rewind()->shouldBeCalled();
        $childrenIterator->next()->shouldBeCalled();

        $productModel->getProductModels()->willReturn(new ArrayCollection());
        $productModel->getProducts()->willReturn($children);

        $productChild->getIdentifier()->willReturn('foo');
        $productIndexer
            ->indexFromProductIdentifiers(
                ['foo'],
                ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2']
            )
            ->shouldBeCalled();

        $productModelRepository->findOneByIdentifier('pm')->willReturn($productModel);

        $this->fromProductModelCode('pm', ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2']);
    }

    function it_indexes_a_product_model_and_its_descendants_product_models_with_options(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository,
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

        $childrenIterator->current()->willReturn($productModelChild);
        $childrenIterator->valid()->willReturn(true, false);
        $childrenIterator->rewind()->shouldBeCalled();
        $childrenIterator->next()->shouldBeCalled();
        $productModelChild->getCode()->willReturn('code');
        $productModelChild->getProducts()->willReturn(new ArrayCollection([]));
        $productModelChild->getProductModels()->willReturn(new ArrayCollection([]));
        $productModelIndexer->indexFromProductModelCodes(['code'], ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2'])
            ->shouldBeCalled();

        $productModelRepository->findOneByIdentifier('pm')->willReturn($productModel);

        $this->fromProductModelCode('pm', ['my_option_key' => 'my_option_value', 'my_option_key2' => 'my_option_value2']);
    }

    function it_does_not_bulk_index_empty_arrays_of_product_models(
        ProductIndexerInterface $productIndexer,
        ProductModelIndexerInterface $productModelIndexer,
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $productIndexer->indexFromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $productModelIndexer->indexFromProductModelCodes(Argument::cetera())->shouldNotBeCalled();

        $this->fromProductModelCodes([]);
    }

    function it_raises_exception_with_non_existing_product_model(
        ProductModelRepositoryInterface $productModelRepository
    ) {
        $productModelRepository->findOneByIdentifier('foo')->willReturn(null);

        $this->shouldThrow(InvalidArgumentException::class)->during('fromProductModelCode', ['foo']);
    }
}
