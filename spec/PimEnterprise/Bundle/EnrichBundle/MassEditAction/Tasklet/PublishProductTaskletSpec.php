<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        UserInterface $userJulia,
        UserInterface $userMary,
        StepExecution $stepExecution
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqbFactory->create(Argument::any())->willReturn($pqb);

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
            $tokenStorage,
            $authorizationChecker,
            $pqbFactory
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->beAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\StepExecutionAwareInterface');
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $pqb,
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $tokenStorage,
        $authorizationChecker,
        $stepExecution,
        JobExecution $jobExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters,
        SearchQueryBuilder $searchQueryBuilder
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(2);

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1, $product2])->shouldBeCalled();

        $pqb->getQueryBuilder()->willReturn($searchQueryBuilder);
        $pqb->setQueryBuilder($searchQueryBuilder)->shouldBeCalled();

        $this->execute();
    }

    function it_executes_a_mass_publish_operation_with_a_configuration_with_invalid_items(
        $pqb,
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $tokenStorage,
        $authorizationChecker,
        $stepExecution,
        JobExecution $jobExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ObjectDetacherInterface $objectDetacher,
        JobParameters $jobParameters,
        SearchQueryBuilder $searchQueryBuilder
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('julia');

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $violation1 = new ConstraintViolation('error1', 'spec', [], '', '', $product1);
        $violation2 = new ConstraintViolation('error2', 'spec', [], '', '', $product2);

        $violations = new ConstraintViolationList([$violation1, $violation2]);

        $stepExecution->incrementSummaryInfo('mass_edited')->shouldNotBeCalled(2);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(2);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $objectDetacher->detach(Argument::any())->shouldBeCalledTimes(2);

        $stepExecution->addWarning(
            Argument::any(),
            [],
            Argument::type('Akeneo\Component\Batch\Item\InvalidItemInterface')
        )->shouldBeCalledTimes(4);

        $manager->publishAll([])->shouldBeCalled();

        $pqb->getQueryBuilder()->willReturn($searchQueryBuilder);
        $pqb->setQueryBuilder($searchQueryBuilder)->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute($configuration);
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $pqb,
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $tokenStorage,
        $authorizationChecker,
        $stepExecution,
        JobExecution $jobExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters,
        SearchQueryBuilder $searchQueryBuilder
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
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($configuration['filters']);
        $jobParameters->get('actions')->willReturn($configuration['actions']);

        $productsPage = [
            [
                $product1,
                $product2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('mary');

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(false);
        $tokenStorage->setToken(Argument::any())->shouldBeCalled();

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $stepExecution->addWarning(
            Argument::any(),
            [],
            Argument::type('Akeneo\Component\Batch\Item\InvalidItemInterface')
        )->shouldBeCalled();

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1])->shouldBeCalled();

        $pqb->getQueryBuilder()->willReturn($searchQueryBuilder);
        $pqb->setQueryBuilder($searchQueryBuilder)->shouldBeCalled();

        $this->execute();
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
