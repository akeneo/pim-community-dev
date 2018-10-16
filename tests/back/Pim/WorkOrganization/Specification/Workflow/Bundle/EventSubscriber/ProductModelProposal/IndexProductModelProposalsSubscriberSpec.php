<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductModelProposal;

use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class IndexProductModelProposalsSubscriberSpec extends ObjectBehavior
{
    function let(ProductModelProposalIndexer $productModelProposalIndexer)
    {
        $this->beConstructedWith($productModelProposalIndexer);
    }

    function it_indexes_product_model_proposal(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $changes = ['values' => ['color']];
        $productModelProposal->getChangesToReview()->willReturn($changes);
        $productModelProposalIndexer->index($productModelProposal)->shouldBeCalled();
        $productModelProposal->setChanges($changes)->shouldBeCalled();
        $productModelProposalIndexer->remove(Argument::any())->shouldNotBeCalled();

        $this->indexProductModelProposal($event)->shouldReturn(null);
    }

    function it_does_not_index_a_single_product_model_proposal_if_unitary_argument_does_not_exist(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);
        $event->hasArgument('unitary')->willReturn(false);

        $productModelProposalIndexer->index($productModelProposal)->shouldNotBeCalled();
        $productModelProposal->setChanges(Argument::any())->shouldNotBeCalled();
        $productModelProposalIndexer->remove(Argument::any())->shouldNotBeCalled();

        $this->indexProductModelProposal($event)->shouldReturn(null);
    }

    function it_does_not_index_a_single_product_model_proposal_if_unitary_argument_is_false(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(false);

        $productModelProposalIndexer->index($productModelProposal)->shouldNotBeCalled();
        $productModelProposal->setChanges(Argument::any())->shouldNotBeCalled();
        $productModelProposalIndexer->remove(Argument::any())->shouldNotBeCalled();

        $this->indexProductModelProposal($event)->shouldReturn(null);
    }

    function it_removes_a_product_model_proposal_if_there_is_no_value_to_index(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);
        $event->hasArgument('unitary')->willReturn(true);
        $event->getArgument('unitary')->willReturn(true);

        $changes = ['values' => []];
        $productModelProposal->getChangesToReview()->willReturn($changes);
        $productModelProposalIndexer->index($productModelProposal)->shouldNotBeCalled();
        $productModelProposal->setChanges(Argument::any())->shouldNotBeCalled();

        $productModelProposal->getId()->willReturn(1);
        $productModelProposalIndexer->remove(1)->shouldBeCalled();

        $this->indexProductModelProposal($event)->shouldReturn(null);
    }

    function it_indexes_several_product_model_proposals(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal1,
        ProductModelDraft $productModelProposal2,
        ProductModelDraft $productModelProposal3
    ) {
        $event->getSubject()->willReturn([$productModelProposal1, $productModelProposal2, $productModelProposal3]);

        $productModelProposal1->getChangesToReview()->willReturn(['values' => ['color']]);
        $productModelProposal1->setChanges(['values' => ['color']])->shouldBeCalled();

        $productModelProposal2->getChangesToReview()->willReturn(['values' => ['color']]);
        $productModelProposal2->setChanges(['values' => ['color']])->shouldBeCalled();

        $productModelProposal3->getChangesToReview()->willReturn(['values' => []]);
        $productModelProposal3->setChanges(Argument::any())->shouldNotBeCalled();

        $productModelProposalIndexer->indexAll([$productModelProposal1, $productModelProposal2])->shouldBeCalled();
        $productModelProposalIndexer->removeAll([$productModelProposal3])->shouldBeCalled();

        $this->bulkIndexProductModelProposals($event)->shouldReturn(null);
    }

    function it_does_not_index_several_product_model_proposals_if_subject_is_not_an_array(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);

        $productModelProposal->getChangesToReview()->shouldNotBeCalled();
        $productModelProposal->setChanges(Argument::any())->shouldNotBeCalled();

        $productModelProposalIndexer->indexAll(Argument::any())->shouldNotBeCalled();
        $productModelProposalIndexer->removeAll(Argument::any())->shouldNotBeCalled();

        $this->bulkIndexProductModelProposals($event)->shouldReturn(null);
    }

    function it_deletes_product_model_proposal_with_generic_event(
        $productModelProposalIndexer,
        GenericEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);
        $productModelProposal->getId()->willReturn(1);
        $productModelProposal->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productModelProposalIndexer->remove(1)->shouldBeCalled();

        $this->deleteProductModelProposal($event)->shouldReturn(null);
    }

    function it_deletes_product_model_proposal_with_remove_event(
        $productModelProposalIndexer,
        RemoveEvent $event,
        ProductModelDraft $productModelProposal
    ) {
        $event->getSubject()->willReturn($productModelProposal);
        $event->getSubjectId()->willReturn(1);
        $productModelProposal->getStatus()->willReturn(EntityWithValuesDraftInterface::READY);
        $productModelProposalIndexer->remove(1)->shouldBeCalled();

        $this->deleteProductModelProposal($event)->shouldReturn(null);
    }
}
