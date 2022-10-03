<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
        AuthorizationCheckerInterface $authorizationChecker,
        StepExecution $stepExecution,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqbFactory->create(Argument::any())->willReturn($pqb);

        $this->beConstructedWith(
            $manager,
            $paginatorFactory,
            $validator,
            $authorizationChecker,
            $pqbFactory,
            $cacheClearer,
            $jobRepository,
            $jobStopper
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf(StepExecutionAwareInterface::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters,
        JobStopper $jobStopper
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

        $cursor->count()->willReturn(2);
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);
        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(true);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(2);
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(2);

        $violations->count()->willReturn(0);

        $product1->getIdentifier()->willReturn('foo');
        $product2->getIdentifier()->willReturn('bar');

        $manager->publishAll([$product1, $product2])->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters,
        JobStopper $jobStopper
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
        $cursor->count()->willReturn(2);
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->willReturn(false);

        $validator->validate($product1)->willReturn($violations);
        $validator->validate($product2)->willReturn($violations);

        $product1->getIdentifier()->willReturn('foo');
        $product1->getIdentifier()->willReturn('bar');

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(2);

        $stepExecution->addWarning(
            Argument::any(),
            [],
            Argument::type(InvalidItemInterface::class)
        )->shouldBeCalled();

        $violations->count()->willReturn(0);

        $manager->publishAll([$product1])->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_skips_product_without_identifier(
        $paginatorFactory,
        $manager,
        $cursor,
        $validator,
        $authorizationChecker,
        $stepExecution,
        ProductInterface $product1,
        ProductInterface $product2,
        ConstraintViolationListInterface $violations,
        JobParameters $jobParameters,
        JobStopper $jobStopper
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
        $cursor->count()->willReturn(2);
        $paginatorFactory->createPaginator($cursor)->willReturn($productsPage);

        $product1->getIdentifier()->willReturn(null);
        $product2->getIdentifier()->willReturn('bar');

        $authorizationChecker->isGranted(Attributes::OWN, $product1)->shouldNotBeCalled();
        $authorizationChecker->isGranted(Attributes::OWN, $product2)->shouldBeCalled()->willReturn(true);

        $validator->validate($product2)->willReturn($violations);
        $violations->count()->willReturn(0);

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('mass_published')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(2);

        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.publish.message.no_identifier',
            [],
            Argument::type(InvalidItemInterface::class)
        )->shouldBeCalled();

        $manager->publishAll([$product2])->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
