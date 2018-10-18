<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UnpublishProductTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ProductQueryBuilder $pqb,
        CursorInterface $cursor,
        AuthorizationCheckerInterface $authorizationChecker,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $pqb->execute()->willReturn($cursor);
        $pqb->addFilter(Argument::cetera())->willReturn($pqb);
        $pqbFactory->create(Argument::any())->willReturn($pqb);

        $this->beConstructedWith(
            $manager,
            $paginatorFactory,
            $validator,
            $authorizationChecker,
            $pqbFactory,
            $cacheClearer
        );
    }

    function it_is_a_configurable_step_element()
    {
        $this->beAnInstanceOf(StepExecutionAwareInterface::class);
    }

    function it_executes_a_mass_publish_operation_with_a_configuration(
        $paginatorFactory,
        $manager,
        $cursor,
        $authorizationChecker,
        $pqbFactory,
        $pqb,
        StepExecution $stepExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2,
        JobParameters $jobParameters
    ) {
        $filters = [
            [
                'field'    => 'sku',
                'operator' => 'IN',
                'value'    => ['1000', '1001']
            ]
        ];

        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);
        $pqbFactory->create(['filters' => $filters])->willReturn($pqb);

        $paginator = [
            [
                $pubProduct1,
                $pubProduct2
            ]
        ];

        $paginatorFactory->createPaginator($cursor)->willReturn($paginator);

        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(true);

        $stepExecution->incrementSummaryInfo('mass_unpublished')->shouldBeCalledTimes(2);

        $manager->unpublishAll([$pubProduct1, $pubProduct2])->shouldBeCalled();

        $this->execute();
    }

    function it_skips_product_when_user_does_not_have_own_right_on_it(
        $paginatorFactory,
        $manager,
        $cursor,
        $authorizationChecker,
        $pqbFactory,
        $pqb,
        StepExecution $stepExecution,
        PublishedProductInterface $pubProduct1,
        PublishedProductInterface $pubProduct2,
        JobParameters $jobParameters
    ) {
        $filters = [
            [
                'field'    => 'sku',
                'operator' => 'IN',
                'value'    => ['1000', '1001']
            ]
        ];

        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filters')->willReturn($filters);
        $pqbFactory->create(['filters' => $filters])->willReturn($pqb);

        $paginator = [
            [
                $pubProduct1,
                $pubProduct2
            ]
        ];
        $paginatorFactory->createPaginator($cursor)->willReturn($paginator);

        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct1)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(false);

        $stepExecution->incrementSummaryInfo('mass_unpublished')->shouldBeCalledTimes(1);


        $authorizationChecker->isGranted(Attributes::OWN, $pubProduct2)->willReturn(false);
        $stepExecution->incrementSummaryInfo('skipped_products')->shouldBeCalledTimes(1);

        $stepExecution->addWarning(
            'pim_enrich.mass_edit_action.unpublish.message.error',
            [],
            Argument::type(DataInvalidItem::class)
        )->shouldBeCalledTimes(1);

        $manager->unpublishAll([$pubProduct1])->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->execute();
    }

    function it_sets_the_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn($this);
    }
}
