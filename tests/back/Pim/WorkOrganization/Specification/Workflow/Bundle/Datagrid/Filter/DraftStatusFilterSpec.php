<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter\DraftStatusFilter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductUuidsByUserAndDraftStatusQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductModelIdsByUserAndDraftStatusQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\FormFactoryInterface;

class DraftStatusFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $formFactory,
        ProductFilterUtility $filterUtility,
        SelectProductUuidsByUserAndDraftStatusQueryInterface $selectProductUuidsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext
    ) {
        $this->beConstructedWith($formFactory, $filterUtility, $selectProductUuidsByUserAndDraftStatusQuery, $selectProductModelIdsByUserAndDraftStatusQuery, $userContext);
    }

    function it_is_a_choice_filter()
    {
        $this->shouldImplement(FilterInterface::class);
        $this->shouldHaveType(ChoiceFilter::class);
    }

    function it_is_the_draft_status_filter()
    {
       $this->shouldHaveType(DraftStatusFilter::class);
    }

    function it_applies_filter_on_draft_status_in_progress(
        ProductFilterUtility $filterUtility,
        SelectProductUuidsByUserAndDraftStatusQueryInterface $selectProductUuidsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext,
        UserInterface $user,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $userContext->getUser()->willReturn($user);
        $user->getUserIdentifier()->willReturn('mary');

        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $selectProductUuidsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([$uuid1, $uuid2]);

        $selectProductModelIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([7]);

        $filterUtility->applyFilter($filterDatasource, 'id', 'IN', ['product_' . $uuid1->toString(), 'product_' . $uuid2->toString(), 'product_model_7'])->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => DraftStatusFilter::IN_PROGRESS]);
    }

    function it_applies_filter_on_draft_status_working_copy(
        ProductFilterUtility $filterUtility,
        SelectProductUuidsByUserAndDraftStatusQueryInterface $selectProductUuidsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext,
        UserInterface $user,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $userContext->getUser()->willReturn($user);
        $user->getUserIdentifier()->willReturn('mary');

        $uuid1 = Uuid::uuid4();
        $uuid2 = Uuid::uuid4();
        $selectProductUuidsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY])
            ->willReturn([$uuid1, $uuid2]);

        $selectProductModelIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY])
            ->willReturn([7]);

        $filterUtility->applyFilter($filterDatasource, 'id', 'NOT IN', ['product_' . $uuid1->toString(), 'product_' . $uuid2->toString(), 'product_model_7'])->shouldBeCalled();

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
        SelectProductUuidsByUserAndDraftStatusQueryInterface $selectProductUuidsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext,
        UserInterface $user,
        FilterDatasourceAdapterInterface $filterDatasource
    ) {
        $userContext->getUser()->willReturn($user);
        $user->getUserIdentifier()->willReturn('mary');

        $selectProductUuidsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([]);

        $selectProductModelIdsByUserAndDraftStatusQuery
            ->execute('mary', [EntityWithValuesDraftInterface::IN_PROGRESS])
            ->willReturn([]);

        $filterUtility->applyFilter($filterDatasource, 'id', 'IN', ['null'])->shouldBeCalled();

        $this->apply($filterDatasource, ['value' => DraftStatusFilter::IN_PROGRESS]);
    }
}
