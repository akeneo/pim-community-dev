<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class PublishProductTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        SecurityContextInterface $securityContext,
        UserInterface $userJulia,
        UserInterface $userMary
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::any(), Argument::any(), Argument::any(), Argument::any())->willReturn($pqb);
        $pqbFactory->create()->willReturn($pqb);

        $userJulia->getRoles()->willReturn(['ProductOwner']);
        $userMary->getRoles()->willReturn(['NotProductOwner']);
        $userManager->findUserByUsername('julia')->willReturn($userJulia);
        $userManager->findUserByUsername('mary')->willReturn($userMary);

        $this->beConstructedWith(
            $manager,
            $paginatorFactory,
            $validator,
            $objectDetacher,
            $userManager,
            $securityContext,
            $pqbFactory
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
        $securityContext,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
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

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $securityContext->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $securityContext->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $securityContext->setToken(Argument::any())->shouldBeCalled();

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(2);

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
        $securityContext,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
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

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $securityContext->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $securityContext->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $securityContext->setToken(Argument::any())->shouldBeCalled();

        $violation1 = new ConstraintViolation('error1', 'spec', [], '', '', $product1);
        $violation2 = new ConstraintViolation('error2', 'spec', [], '', '', $product2);

        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldNotBeCalled(2);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(2);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $objectDetacher->detach(Argument::any())->shouldBeCalledTimes(2);

        $stepExecution->addWarning('publish_product_tasklet', Argument::any(), [], $product1)->shouldBeCalledTimes(2);
        $stepExecution->addWarning('publish_product_tasklet', Argument::any(), [], $product2)->shouldBeCalledTimes(2);

        $manager->publishAll([])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $securityContext,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
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

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('mary');

        $securityContext->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $securityContext->isGranted(Attributes::OWN, $product2)->willReturn(false);
        $securityContext->setToken(Argument::any())->shouldBeCalled();

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $stepExecution->addWarning('publish_product_tasklet', Argument::any(), [], $product2)->shouldBeCalled();

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1])->shouldBeCalled();

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
