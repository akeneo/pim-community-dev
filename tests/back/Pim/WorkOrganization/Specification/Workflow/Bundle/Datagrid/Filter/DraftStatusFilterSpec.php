<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter\DraftStatusFilter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductIdsByUserAndDraftStatusQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductModelIdsByUserAndDraftStatusQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DraftStatusFilterSpec extends ObjectBehavior
{
    function let(
        ChoiceFilter $choiceFilter,
        ProductFilterUtility $filterUtility,
        SelectProductIdsByUserAndDraftStatusQueryInterface $selectProductIdsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext
    ) {
        $this->beConstructedWith($choiceFilter, $filterUtility, $selectProductIdsByUserAndDraftStatusQuery, $selectProductModelIdsByUserAndDraftStatusQuery, $userContext);
    }

    function it_is_a_filter()
    {
       $this->shouldImplement(FilterInterface::class);
    }

    function it_is_the_draft_status_filter()
    {
       $this->shouldHaveType(DraftStatusFilter::class);
    }

    function it_applies_filter_on_draft_status_in_progress(
        ProductFilterUtility $filterUtility,
        SelectProductIdsByUserAndDraftStatusQueryInterface $selectProductIdsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext,
        UserInterface $user,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('mary');

        $selectProductIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([42, 56]);

        $selectProductModelIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([7]);

        $filterUtility->applyFilter($filterDatasource, 'id', 'IN', ['product_42', 'product_56', 'product_model_7'])->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => DraftStatusFilter::IN_PROGRESS]);
    }

    function it_applies_filter_on_draft_status_working_copy(
        ProductFilterUtility $filterUtility,
        SelectProductIdsByUserAndDraftStatusQueryInterface $selectProductIdsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext,
        UserInterface $user,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('mary');

        $selectProductIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY])
            ->willReturn([42, 56]);

        $selectProductModelIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY])
            ->willReturn([7]);

        $filterUtility->applyFilter($filterDatasource, 'id', 'NOT IN', ['product_42', 'product_56', 'product_model_7'])->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => DraftStatusFilter::WORKING_COPY]);
    }

    function it_does_nothing_if_there_is_no_filter_value(ProductFilterUtility $filterUtility, FilterDatasourceAdapterInterface $filterDatasource)
    {
        $this->apply($filterDatasource, ['value' => null]);

        $filterUtility->applyFilter(Argument::cetera())->shouldNotBeCalled();
    }

    function it_throws_an_exception_if_the_filter_value_is_not_supported(FilterDatasourceAdapterInterface $filterDatasource)
    {
        $this->shouldThrow(\LogicException::class)->during('apply', [
            $filterDatasource, ['value' => 33]
        ]);
    }

    function it_throws_an_exception_if_the_user_is_not_authenticated(
        FilterDatasourceAdapterInterface $filterDatasource,
        UserContext $userContext
    ) {
        $userContext->getUser()->willReturn(null);

        $this->shouldThrow(\Exception::class)->during('apply', [
            $filterDatasource, ['value' => EntityWithValuesDraftInterface::IN_PROGRESS]
        ]);
    }

    function it_applies_a_filter_even_if_there_is_no_draft(
        ProductFilterUtility $filterUtility,
        SelectProductIdsByUserAndDraftStatusQueryInterface $selectProductIdsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext,
        UserInterface $user,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $userContext->getUser()->willReturn($user);
        $user->getUsername()->willReturn('mary');

        $selectProductIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([]);

        $selectProductModelIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([]);

        $filterUtility->applyFilter($filterDatasource, 'id', 'IN', ['null'])->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => DraftStatusFilter::IN_PROGRESS]);
    }
}
