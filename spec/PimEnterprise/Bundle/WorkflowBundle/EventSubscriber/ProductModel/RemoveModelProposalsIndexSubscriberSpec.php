<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductModel;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\DescendantProductIdsQueryInterface;
use Pim\Component\Catalog\ProductModel\Query\DescendantProductModelIdsQueryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductProposalIndexer;
use PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductModel\RemoveModelProposalsIndexSubscriber;
use PimEnterprise\Component\Workflow\Query\SelectModelProposalIdsFromProductModelIdsQueryInterface;
use PimEnterprise\Component\Workflow\Query\SelectProposalIdsFromProductIdsQueryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveModelProposalsIndexSubscriberSpec extends ObjectBehavior
{
    function let(
        SelectModelProposalIdsFromProductModelIdsQueryInterface $selectModelProposalIdsFromProductModelIdsQuery,
        SelectProposalIdsFromProductIdsQueryInterface $selectProposalIdsFromProductIdsQuery,
        DescendantProductIdsQueryInterface $descendantProductIdsQuery,
        DescendantProductModelIdsQueryInterface $descendantProductModelIdsQuery,
        ProductModelProposalIndexer $productModelProposalIndexer,
        ProductProposalIndexer $productProposalIndexer
    ) {
        $this->beConstructedWith(
            $selectModelProposalIdsFromProductModelIdsQuery,
            $selectProposalIdsFromProductIdsQuery,
            $descendantProductIdsQuery,
            $descendantProductModelIdsQuery,
            $productModelProposalIndexer,
            $productProposalIndexer
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveModelProposalsIndexSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_remove_events()
    {
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::PRE_REMOVE);
        $this->getSubscribedEvents()->shouldHaveKey(StorageEvents::POST_REMOVE);
    }

    function it_calculates_impacted_model_proposals_on_product_model_pre_remove_event(
        $descendantProductModelIdsQuery,
        $descendantProductIdsQuery,
        $selectModelProposalIdsFromProductModelIdsQuery,
        $selectProposalIdsFromProductIdsQuery,
        ProductModelInterface $productModel
    ) {
        $productModel->getId()->willReturn(2);
        $event = new GenericEvent($productModel->getWrappedObject());

        $descendantProductModelIdsQuery->fetchFromParentProductModelId(2)->willReturn([20, 23]);
        $descendantProductIdsQuery->fetchFromProductModelIds([20, 23, 2])->willReturn([201, 202, 231]);

        $selectModelProposalIdsFromProductModelIdsQuery->fetch([20, 23, 2])->shouldBeCalled();
        $selectProposalIdsFromProductIdsQuery->fetch([201, 202, 231])->shouldBeCalled();

        $this->calculateImpactedModelProposals($event)->shouldReturn(null);
    }

    function it_has_no_impact_on_non_product_model(ProductInterface $product)
    {
        $event = new GenericEvent($product);
        $product->getId()->shouldNotBeCalled();

        $this->calculateImpactedModelProposals($event)->shouldReturn(null);
    }

    function it_does_not_remove_model_product_proposals_index_when_no_proposals_impacted($productModelProposalIndexer)
    {
        $productModelProposalIndexer->removeAll(Argument::any())->shouldNotBeCalled();

        $this->removeProductModelProposals(new GenericEvent())->shouldReturn(null);
    }

    function it_does_not_remove_product_proposals_index_when_no_proposals_impacted($productProposalIndexer)
    {
        $productProposalIndexer->removeAll(Argument::any())->shouldNotBeCalled();

        $this->removeProductModelProposals(new GenericEvent())->shouldReturn(null);
    }

    function it_removes_product_model_proposals_index_on_impacted_proposals(
        $selectModelProposalIdsFromProductModelIdsQuery,
        $selectProposalIdsFromProductIdsQuery,
        $descendantProductIdsQuery,
        $descendantProductModelIdsQuery,
        $productModelProposalIndexer,
        $productProposalIndexer,
        ProductModelInterface $productModel
    ) {
        $productModel->getId()->willReturn(2);
        $event = new GenericEvent($productModel->getWrappedObject());

        $descendantProductModelIdsQuery->fetchFromParentProductModelId(2)->willReturn([20, 23]);
        $descendantProductIdsQuery->fetchFromProductModelIds([20, 23, 2])->willReturn([201, 202, 231]);

        $selectModelProposalIdsFromProductModelIdsQuery->fetch([20, 23, 2])->willReturn([2003, 2332]);
        $selectProposalIdsFromProductIdsQuery->fetch([201, 202, 231])->willReturn([2010, 2310, 2314]);

        $productModelProposalIndexer->removeAll([2003, 2332])->shouldBeCalled();
        $productProposalIndexer->removeAll([2010, 2310, 2314])->shouldBeCalled();

        $this->calculateImpactedModelProposals($event)->shouldReturn(null);
        $this->removeProductModelProposals($event)->shouldReturn(null);
    }
}
