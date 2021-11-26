<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelHandler;
use Akeneo\Pim\Enrichment\Component\Product\Job\DeleteProductsAndProductModelsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountVariantProductsInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CountProductModelsAndChildrenProductModelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeleteProductsAndProductModelsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        BulkRemoverInterface $productRemover,
        RemoveProductModelHandler $removeProductModelHandler,
        ObjectFilterInterface $filter,
        EntityManagerClearerInterface $cacheClearer,
        CountProductModelsAndChildrenProductModelsInterface $countProductModelsAndChildrenProductModels,
        CountVariantProductsInterface $countVariantProducts,
        JobStopper $jobStopper,
        JobRepositoryInterface $jobRepository,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $pqbFactory,
            $productRemover,
            $removeProductModelHandler,
            $cacheClearer,
            $filter,
            2,
            $countProductModelsAndChildrenProductModels,
            $countVariantProducts,
            $jobStopper,
            $jobRepository,
            $validator
        );
    }

    function it_is_a_tasklet()
    {
        $this->shouldHaveType(DeleteProductsAndProductModelsTasklet::class);
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_deletes_products(
        $pqbFactory,
        $productRemover,
        RemoveProductModelHandler $removeProductModelHandler,
        $filter,
        $cacheClearer,
        $countProductModelsAndChildrenProductModels,
        $countVariantProducts,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $countItemPQB,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $countItemCursor,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductInterface $product123,
        ProductInterface $product456,
        ProductInterface $product789,
        JobStopper $jobStopper
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
            ->willReturn($countItemPQB, $rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $countItemPQB->execute()->willReturn($countItemCursor);
        $countItemCursor->count()->shouldBeCalled()->willReturn(3);

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
        $variantProductsPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(true, false);
        $variantProductsCursor->current()->willReturn($product789);
        $variantProductsCursor->next()->shouldBeCalled();

        $filter
            ->filterObject(Argument::any(), 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $countProductModelsAndChildrenProductModels->forProductModelCodes([])->willReturn(0);
        $countVariantProducts->forProductModelCodes([])->willReturn(0);

        $productRemover->removeAll([$product789])->shouldBeCalled();
        $productRemover->removeAll([$product123, $product456])->shouldBeCalled();
        $removeProductModelHandler->__invoke(Argument::any())->shouldNotBeCalled();

        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementReadCount()->shouldBeCalledTimes(3);

        $stepExecution->incrementSummaryInfo('deleted_products', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 1)->shouldBeCalled();

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(0);

        $stepExecution->incrementSummaryInfo('deleted_product_models', 0)->shouldBeCalledTimes(2);

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_deletes_product_models(
        $pqbFactory,
        $productRemover,
        RemoveProductModelHandler $removeProductModelHandler,
        $cacheClearer,
        $filter,
        $countProductModelsAndChildrenProductModels,
        $countVariantProducts,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $countItemPQB,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $countItemCursor,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductModelInterface $productModel123,
        ProductModelInterface $productModel456,
        ProductModelInterface $productModel789,
        JobStopper $jobStopper,
        ValidatorInterface $validator
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
            ->willReturn($countItemPQB, $rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $countItemPQB->execute()->willReturn($countItemCursor);
        $countItemCursor->count()->shouldBeCalled()->willReturn(3);

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
        $variantProductsPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(false);

        $filter
            ->filterObject(Argument::any(), 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $productModel123->getCode()->willReturn('product_model_123_code');
        $productModel456->getCode()->willReturn('product_model_456_code');
        $productModel789->getCode()->willReturn('product_model_789_code');

        $countVariantProducts->forProductModelCodes(['product_model_789_code'])->willReturn(0);
        $countVariantProducts->forProductModelCodes(['product_model_123_code', 'product_model_456_code'])->willReturn(0);
        $countProductModelsAndChildrenProductModels->forProductModelCodes(['product_model_789_code'])->willReturn(2);
        $countProductModelsAndChildrenProductModels->forProductModelCodes(['product_model_123_code', 'product_model_456_code'])->willReturn(1);

        $removePMCommand1 = new RemoveProductModelCommand('product_model_123_code');
        $removePMCommand2 = new RemoveProductModelCommand('product_model_456_code');
        $removePMCommand3 = new RemoveProductModelCommand('product_model_789_code');
        $validator->validate($removePMCommand1)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $validator->validate($removePMCommand2)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $validator->validate($removePMCommand3)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $removeProductModelHandler->__invoke($removePMCommand1)->shouldBeCalled();
        $removeProductModelHandler->__invoke($removePMCommand2)->shouldBeCalled();
        $removeProductModelHandler->__invoke($removePMCommand3)->shouldBeCalled();

        $productRemover->removeAll([])->shouldBeCalled();

        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementReadCount()->shouldBeCalledTimes(3);

        $stepExecution->incrementSummaryInfo('deleted_product_models', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_product_models', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_products', 0)->shouldBeCalledTimes(2);

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(0);

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_deletes_products_and_product_models(
        $pqbFactory,
        $productRemover,
        RemoveProductModelHandler $removeProductModelHandler,
        $cacheClearer,
        $filter,
        $countProductModelsAndChildrenProductModels,
        $countVariantProducts,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $countItemPQB,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $countItemCursor,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductModelInterface $productModel3,
        ProductInterface $product4,
        ProductInterface $product5,
        ProductInterface $product6,
        JobStopper $jobStopper,
        ValidatorInterface $validator
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
            ->willReturn($countItemPQB, $rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $countItemPQB->execute()->willReturn($countItemCursor);
        $countItemCursor->count()->shouldBeCalled()->willReturn(6);

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
        $variantProductsPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
        $variantProductsPQB->execute()->willReturn($variantProductsCursor);
        $variantProductsCursor->valid()->willReturn(true, true, false);
        $variantProductsCursor->current()->willReturn($product4, $product5);
        $variantProductsCursor->next()->shouldBeCalled();

        $filter
            ->filterObject(Argument::any(), 'pim.enrich.product.delete')
            ->shouldBeCalled()
            ->willReturn(false);

        $productModel1->getCode()->willReturn('product_model_1_code');
        $productModel2->getCode()->willReturn('product_model_2_code');
        $productModel3->getCode()->willReturn('product_model_3_code');

        $countVariantProducts->forProductModelCodes([])->willReturn(0);
        $countVariantProducts->forProductModelCodes(['product_model_3_code'])->willReturn(0);
        $countVariantProducts->forProductModelCodes(['product_model_1_code', 'product_model_2_code'])->willReturn(0);
        $countProductModelsAndChildrenProductModels->forProductModelCodes([])->willReturn(0);
        $countProductModelsAndChildrenProductModels->forProductModelCodes(['product_model_3_code'])->willReturn(1);
        $countProductModelsAndChildrenProductModels->forProductModelCodes(['product_model_1_code', 'product_model_2_code'])->willReturn(2);

        $stepExecution->addSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->addSummaryInfo('deleted_product_models', 0)->shouldBeCalled();

        $productRemover->removeAll([$product4, $product5])->shouldBeCalled();
        $productRemover->removeAll([])->shouldBeCalled();
        $productRemover->removeAll([$product6])->shouldBeCalled();

        $removePMCommand1 = new RemoveProductModelCommand('product_model_1_code');
        $removePMCommand2 = new RemoveProductModelCommand('product_model_2_code');
        $removePMCommand3 = new RemoveProductModelCommand('product_model_3_code');
        $validator->validate($removePMCommand1)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $validator->validate($removePMCommand2)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $validator->validate($removePMCommand3)->shouldBeCalled()->willReturn(new ConstraintViolationList([
            new ConstraintViolation('error_message', null, [], null, null, null),
        ]));
        $removeProductModelHandler->__invoke($removePMCommand1)->shouldBeCalled();
        $removeProductModelHandler->__invoke($removePMCommand2)->shouldBeCalled();
        $removeProductModelHandler->__invoke($removePMCommand3)->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 1)->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('skipped_deleted_product_models', 1)->shouldBeCalled();
        $stepExecution->addWarning('error_message', [], Argument::type(DataInvalidItem::class))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 2)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 2)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 0)->shouldBeCalled();
        $stepExecution->incrementReadCount()->shouldBeCalledTimes(6);

        $stepExecution->setTotalItems(6)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(2)->shouldBeCalledTimes(2);
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledTimes(1);
        $stepExecution->incrementProcessedItems(0);

        $cacheClearer->clear()->shouldBeCalledTimes(3);

        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_deletes_owned_products_and_product_models(
        $pqbFactory,
        $productRemover,
        RemoveProductModelHandler $removeProductModelHandler,
        $cacheClearer,
        $filter,
        $countProductModelsAndChildrenProductModels,
        $countVariantProducts,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        ProductQueryBuilderInterface $countItemPQB,
        ProductQueryBuilderInterface $rootProductModelPQB,
        ProductQueryBuilderInterface $subProductModelPQB,
        ProductQueryBuilderInterface $variantProductsPQB,
        CursorInterface $countItemCursor,
        CursorInterface $rootProductModelCursor,
        CursorInterface $subProductModelCursor,
        CursorInterface $variantProductsCursor,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        ProductInterface $product1,
        ProductInterface $product2,
        JobStopper $jobStopper,
        ValidatorInterface $validator
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
            ->willReturn($countItemPQB, $rootProductModelPQB, $subProductModelPQB, $variantProductsPQB);

        $countItemPQB->execute()->willReturn($countItemCursor);
        $countItemCursor->count()->shouldBeCalled()->willReturn(4);

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
        $variantProductsPQB->addFilter('parent', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();
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

        $productModel1->getCode()->willReturn('product_model_1_code');

        $countVariantProducts->forProductModelCodes(['product_model_1_code'])->willReturn(0);
        $countProductModelsAndChildrenProductModels->forProductModelCodes(['product_model_1_code'])->willReturn(1);

        $productRemover->removeAll([$product1])->shouldBeCalled();

        $countVariantProducts->forProductModelCodes([])->willReturn(0);
        $countProductModelsAndChildrenProductModels->forProductModelCodes([])->willReturn(0);

        $productRemover->removeAll([])->shouldBeCalled([]);

        $removePMCommand1 = new RemoveProductModelCommand('product_model_1_code');
        $validator->validate($removePMCommand1)->shouldBeCalled()->willReturn(new ConstraintViolationList());
        $removeProductModelHandler->__invoke($removePMCommand1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 1)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 1)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('deleted_product_models', 0)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('deleted_products', 0)->shouldBeCalled();

        $stepExecution->incrementReadCount()->shouldBeCalledTimes(4);

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(1)->shouldBeCalledTimes(4);
        $stepExecution->incrementProcessedItems(0);

        $cacheClearer->clear()->shouldBeCalledTimes(2);

        $jobStopper->isStopping($stepExecution)->willReturn(false);

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
