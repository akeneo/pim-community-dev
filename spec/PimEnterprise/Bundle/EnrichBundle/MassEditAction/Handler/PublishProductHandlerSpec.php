<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class PublishProductHandlerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($pqb);
        $pqbFactory->create()->willReturn($pqb);

        $this->beConstructedWith(
            $pqbFactory,
            $manager,
            $paginatorFactory,
            $validator,
            $objectDetacher
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\StepExecutionAwareInterface');
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1000', '1001']
                ]
            ],
            'actions' => []
        ];
        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1, $product2])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_executes_a_mass_publish_operation_with_a_configuration_with_invalid_items(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        StepExecution $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ObjectDetacherInterface $objectDetacher
    ) {
        $configuration = [
            'filters' => [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['1000', '1001']
                ]
            ],
            'actions' => []
        ];
        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $violation1 = new ConstraintViolation('error1', 'spec', [], '', '', $product1);
        $violation2 = new ConstraintViolation('error2', 'spec', [], '', '', $product2);

        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldNotBeCalled(2);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(2);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $objectDetacher->detach(Argument::any())->shouldBeCalledTimes(2);

        $stepExecution->addWarning('publish_product_handler', Argument::any(), [], $product1)->shouldBeCalledTimes(2);
        $stepExecution->addWarning('publish_product_handler', Argument::any(), [], $product2)->shouldBeCalledTimes(2);

        $manager->publishAll([])->shouldBeCalled();

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
