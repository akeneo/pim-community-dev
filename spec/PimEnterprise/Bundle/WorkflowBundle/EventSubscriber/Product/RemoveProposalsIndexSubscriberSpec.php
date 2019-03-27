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

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Product;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductProposalIndexer;
use PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Product\RemoveProposalsIndexSubscriber;
use PimEnterprise\Component\Workflow\Query\SelectProposalIdsFromProductIdsQueryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveProposalsIndexSubscriberSpec extends ObjectBehavior
{
    function let(
        SelectProposalIdsFromProductIdsQueryInterface $query,
        ProductProposalIndexer $indexer
    ) {
        $this->beConstructedWith($query, $indexer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoveProposalsIndexSubscriber::class);
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

    function it_calculates_impacted_proposals_on_product_pre_remove_event($query, ProductInterface $product)
    {
        $product->getId()->willReturn(44);
        $event = new GenericEvent($product->getWrappedObject());

        $query->fetch([44])->shouldBeCalled();

        $this->calculateImpactedProposals($event)->shouldReturn(null);
    }

    function it_has_no_impact_on_non_product($query)
    {
        $event = new GenericEvent(ProductModelInterface::class);

        $query->fetch(Argument::any())->shouldNotBeCalled();

        $this->calculateImpactedProposals($event)->shouldReturn(null);
    }

    function it_does_not_remove_product_proposals_index_when_no_proposals_impacted($indexer)
    {
        $indexer->removeAll(Argument::any())->shouldNotBeCalled();

        $this->removeProductProposals(new GenericEvent())->shouldReturn(null);
    }

    function it_removes_product_proposals_index_on_impacted_proposals($query, $indexer, ProductInterface $product)
    {
        $product->getId()->willReturn(44);
        $event = new GenericEvent($product->getWrappedObject());
        $query->fetch([44])->willReturn([55, 12, 31]);

        $indexer->removeAll([55, 12, 31])->shouldBeCalled();

        $this->calculateImpactedProposals($event)->shouldReturn(null);
        $this->removeProductProposals($event)->shouldReturn(null);
    }
}
