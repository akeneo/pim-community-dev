<?php

namespace spec\Pim\Component\Enrich\Job;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductAndProductModel\Query\CountProductVariantsInterface;
use Pim\Component\Catalog\ProductModel\Query\CountProductModelChildrenInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use Pim\Component\Enrich\Job\DeleteProductsAndProductModelsTasklet;
use Prophecy\Argument;

class DeleteProductsAndProductModelsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        BulkRemoverInterface $productRemover,
        BulkRemoverInterface $productModelRemover,
        ObjectFilterInterface $filter,
        EntityManagerClearerInterface $cacheClearer,
        CountProductModelChildrenInterface $countProductModelChildren,
        CountProductVariantsInterface $countProductVariants
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $productRemover,
            $productModelRemover,
            $cacheClearer,
            $filter,
            2,
            $countProductModelChildren,
            $countProductVariants
        );
    }

    function it_is_a_tasklet()
    {
        $this->shouldHaveType(DeleteProductsAndProductModelsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_deletes_products(
        $pqbFactory,
        $productRemover,
        $productModelRemover,
        $filter,
        $cacheClearer,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductInterface $product123,
        ProductInterface $product456,
        ProductInterface $product789
    ) {
        $this->setStepExecution($stepExecution);
        $filters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'values' => ['product_123', 'product_456', 'product_789']
            ]
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $pqbFactory->create(['filters' => $filters])
            ->willReturn($rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $rootProductModelPQB->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $rootProductModelPQB->execute()->willReturn($rootProductModelCursor);
        $rootProductModelCursor->valid()->willReturn(true, true, false);
        $rootProductModelCursor->current()->willReturn($product123, $product456);
        $rootProductModelCursor->next()->shouldBeCalled();

        $subProductModelPQB->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class)->shouldBeCalled();
        $subProductModelPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $subProductModelPQB->execute()->willReturn($subProductModelCursor);
        $subProductModelCursor->valid()->willReturn(false);

        $variantProductsPQB->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(true, false);
        $variantProductsCursor->current()->willReturn($product789);
        $variantProductsCursor->next()->shouldBeCalled();

        $filter
            ->filterObject(Argument::any(), 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $productRemover->removeAll([$product789])->shouldBeCalled();
        $productRemover->removeAll([$product123, $product456])->shouldBeCalled();
        $productModelRemover->removeAll([])->shouldBeCalled();

        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementReadCount()->shouldBeCalledTimes(3);

        $stepExecution->incrementSummaryInfo('deleted_products', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 0)->shouldBeCalledTimes(2);

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $this->execute();
    }

    function it_deletes_product_models(
        $pqbFactory,
        $productRemover,
        $productModelRemover,
        $cacheClearer,
        $filter,
        $countProductModelChildren,
        $countProductVariants,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductModelInterface $productModel123,
        ProductModelInterface $productModel456,
        ProductModelInterface $productModel789
    ) {
        $this->setStepExecution($stepExecution);
        $filters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'values' => ['product_model_123', 'product_model_456', 'product_model_789']
            ]
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $pqbFactory->create(['filters' => $filters])
            ->willReturn($rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $rootProductModelPQB->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $rootProductModelPQB->execute()->willReturn($rootProductModelCursor);
        $rootProductModelCursor->valid()->willReturn(true, false);
        $rootProductModelCursor->current()->willReturn($productModel789);
        $rootProductModelCursor->next()->shouldBeCalled();

        $subProductModelPQB->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class)->shouldBeCalled();
        $subProductModelPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $subProductModelPQB->execute()->willReturn($subProductModelCursor);
        $subProductModelCursor->valid()->willReturn(true, true, false);
        $subProductModelCursor->current()->willReturn($productModel123, $productModel456);
        $subProductModelCursor->next()->shouldBeCalled();

        $variantProductsPQB->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(false);

        $filter
            ->filterObject(Argument::any(), 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $countProductVariants->forProductModels([$productModel789])->willReturn(0);
        $countProductVariants->forProductModels([$productModel123, $productModel456])->willReturn(0);
        $countProductModelChildren->forProductModels([$productModel789])->willReturn(2);
        $countProductModelChildren->forProductModels([$productModel123, $productModel456])->willReturn(1);

        $productModelRemover->removeAll([$productModel123, $productModel456])->shouldBeCalled();
        $productModelRemover->removeAll([$productModel789])->shouldBeCalled();
        $productRemover->removeAll([])->shouldBeCalled();

        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementReadCount()->shouldBeCalledTimes(3);

        $stepExecution->incrementSummaryInfo('deleted_product_models', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_product_models', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_products', 0)->shouldBeCalledTimes(2);

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $this->execute();
    }

    function it_deletes_products_and_product_models(
        $pqbFactory,
        $productRemover,
        $productModelRemover,
        $cacheClearer,
        $filter,
        $countProductModelChildren,
        $countProductVariants,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ProductInterface $product4,
        ProductInterface $product5,
        ProductInterface $product6
    ) {
        $this->setStepExecution($stepExecution);
        $filters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'values' => [
                    'product_model_1',
                    'product_model_2',
                    'product_model_3',
                    'product_4',
                    'product_5',
                    'product_6',
                ]
            ]
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $pqbFactory->create(['filters' => $filters])
            ->willReturn($rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $rootProductModelPQB->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $rootProductModelPQB->execute()->willReturn($rootProductModelCursor);
        $rootProductModelCursor->valid()->willReturn(true, true, false);
        $rootProductModelCursor->current()->willReturn($productModel3, $product6);
        $rootProductModelCursor->next()->shouldBeCalled();

        $subProductModelPQB->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class)->shouldBeCalled();
        $subProductModelPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $subProductModelPQB->execute()->willReturn($subProductModelCursor);
        $subProductModelCursor->valid()->willReturn(true, true, false);
        $subProductModelCursor->current()->willReturn($productModel1, $productModel2);
        $subProductModelCursor->next()->shouldBeCalled();

        $variantProductsPQB->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(true, true, false);
        $variantProductsCursor->current()->willReturn($product4, $product5);
        $variantProductsCursor->next()->shouldBeCalled();

        $filter
            ->filterObject(Argument::any(), 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $countProductVariants->forProductModels([$productModel3])->willReturn(0);
        $countProductVariants->forProductModels([$productModel1, $productModel2])->willReturn(0);
        $countProductModelChildren->forProductModels([$productModel3])->willReturn(1);
        $countProductModelChildren->forProductModels([$productModel1, $productModel2])->willReturn(2);

        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();

        $productRemover->removeAll([$product4, $product5])->shouldBeCalled();
        $productModelRemover->removeAll([])->shouldBeCalled([]);

        $productRemover->removeAll([])->shouldBeCalled();
        $productModelRemover->removeAll([$productModel1, $productModel2])->shouldBeCalled([]);

        $productModelRemover->removeAll([$productModel3])->shouldBeCalled();
        $productRemover->removeAll([$product6])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 2)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 0)->shouldBeCalled();

        $stepExecution->incrementReadCount()->shouldBeCalledTimes(6);

        $cacheClearer->clear()->shouldBeCalledTimes(3);

        $this->execute();
    }

    function it_deletes_owned_products_and_product_models(
        $pqbFactory,
        $productRemover,
        $productModelRemover,
        $cacheClearer,
        $filter,
        $countProductModelChildren,
        $countProductVariants,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductInterface $product1,
        ProductInterface $product2
    ) {
        $this->setStepExecution($stepExecution);
        $filters = [
            [
                'field' => 'id',
                'operator' => 'IN',
                'values' => [
                    'product_model_1',
                    'product_model_2',
                    'product_1',
                    'product_2',
                ]
            ]
        ];

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);

        $pqbFactory->create(['filters' => $filters])
            ->willReturn($rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $rootProductModelPQB->addFilter('parent', Operators::IS_EMPTY, null)->shouldBeCalled();
        $rootProductModelPQB->execute()->willReturn($rootProductModelCursor);
        $rootProductModelCursor->valid()->willReturn(true, true, true, true, false);
        $rootProductModelCursor->current()->willReturn($productModel1, $productModel2, $product1, $product2);
        $rootProductModelCursor->next()->shouldBeCalled();

        $subProductModelPQB->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class)->shouldBeCalled();
        $subProductModelPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $subProductModelPQB->execute()->willReturn($subProductModelCursor);
        $subProductModelCursor->valid()->willReturn(false);

        $variantProductsPQB->addFilter('entity_type', Operators::EQUALS, ProductInterface::class)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(false);

        $filter
            ->filterObject($productModel1, 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $filter
            ->filterObject($productModel2, 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(true);

        $filter
            ->filterObject($product1, 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $filter
            ->filterObject($product2, 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(true);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();

        $countProductVariants->forProductModels([$productModel1])->willReturn(0);
        $countProductModelChildren->forProductModels([$productModel1])->willReturn(1);

        $productRemover->removeAll([$product1])->shouldBeCalled();
        $productModelRemover->removeAll([$productModel1])->shouldBeCalled();

        $productModelRemover->removeAll([])->shouldBeCalled();
        $productRemover->removeAll([])->shouldBeCalled([]);

        $stepExecution->incrementSummaryInfo('deleted_product_models', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 0)->shouldBeCalled();

        $stepExecution->incrementReadCount()->shouldBeCalledTimes(4);

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $this->execute();
    }

    function it_throws_an_exception_if_step_execution_is_not_set()
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'In order to execute "%s" you need to set a step execution.',
                        DeleteProductsAndProductModelsTasklet::class
                    )
                )
            )
            ->during('execute');
    }
}
