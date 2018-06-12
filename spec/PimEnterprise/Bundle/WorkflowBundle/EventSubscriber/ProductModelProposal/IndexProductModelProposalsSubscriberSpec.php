<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductModelProposal;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Event;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use PimEnterprise\Component\Workflow\Exception\PublishedProductConsistencyException;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;
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
