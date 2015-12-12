<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UnpublishProductHandlerSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
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
            $authorizationChecker,
            $pqbFactory
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $paginatorFactory,
        $manager,
        $cursor,
        $authorizationChecker,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2
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
                $pubProduct1,
                $pubProduct2
            ]
        ];

        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(true);
        $authorizationChecker->setToken(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('mass_unpublished')->shouldBeCalledTimes(2);

        $manager->unpublishAll([$pubProduct1, $pubProduct2])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $paginatorFactory,
        $manager,
        $cursor,
        $authorizationChecker,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2
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
                $pubProduct1,
                $pubProduct2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('mary');

        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(false);
        $authorizationChecker->setToken(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('mass_unpublished')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $stepExecution->addWarning('unpublish_product_tasklet', Argument::any(), [], $pubProduct2)->shouldBeCalled();

        $manager->unpublishAll([$pubProduct1])->shouldBeCalled();

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
