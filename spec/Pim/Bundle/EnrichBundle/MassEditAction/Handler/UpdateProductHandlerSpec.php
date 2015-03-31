<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class UpdateProductHandlerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductUpdaterInterface $productUpdater,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        PaginatorFactoryInterface $paginatorFactory,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        ValidatorInterface $validator
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($pqb);
        $pqbFactory->create()->willReturn($pqb);

        $this->beConstructedWith(
            $pqbFactory,
            $productUpdater,
            $productSaver,
            $objectDetacher,
            $paginatorFactory,
            $validator
        );
    }

    function it_executes_the_update_operation_with_a_configuration(
        $productUpdater,
        $productSaver,
        $paginatorFactory,
        $cursor,
        $validator,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations
    ) {
        $configuration = [
            'filters' =>
            [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1000', '1001'],
                ]
            ],
            'actions' =>
            [
                [
                    'field' => 'enabled',
                    'value' => false,
                ]
            ]
        ];

        $productsPage = [
            [
                $product1,
                $product2,
            ]
        ];

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $violations->count()->willReturn(0);

        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $productUpdater->setData($product1, 'enabled', false)->shouldBeCalled();
        $productUpdater->setData($product2, 'enabled', false)->shouldBeCalled();

        $productSaver->saveAll([$product1, $product2], Argument::type('array'))->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->execute($configuration);
    }

    function it_executes_the_update_operation_with_a_configuration_and_skips_invalid_items(
        $productUpdater,
        $productSaver,
        $paginatorFactory,
        $cursor,
        $validator,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ObjectDetacherInterface $objectDetacher
    ) {
        $configuration = [
            'filters' =>
            [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1000', '1001'],
                ]
            ],
            'actions' =>
            [
                [
                    'field' => 'enabled',
                    'value' => false,
                ]
            ]
        ];

        $productsPage = [
            [
                $product1,
                $product2,
            ]
        ];

        $violation1 = new ConstraintViolation('error1', 'spec', [], '', '', $product1);
        $violation2 = new ConstraintViolation('error2', 'spec', [], '', '', $product2);

        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('skip_products')->shouldBeCalledTimes(2);

        $productUpdater->setData($product1, 'enabled', false)->shouldBeCalled();
        $productUpdater->setData($product2, 'enabled', false)->shouldBeCalled();

        $stepExecution->addWarning('update_product_handler', Argument::any(), [], $product1)->shouldBeCalledTimes(2);
        $stepExecution->addWarning('update_product_handler', Argument::any(), [], $product2)->shouldBeCalledTimes(2);

        $objectDetacher->detach($product1)->shouldBeCalled();
        $objectDetacher->detach($product2)->shouldBeCalled();

        $productSaver->saveAll([$product1, $product2], Argument::type('array'))->shouldBeCalled();

        $this->setStepExecution($stepExecution);

        $this->execute($configuration);
    }

    function it_returns_the_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
