<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft\MongoDBODM;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class RemoveOutdatedProductDraftSubscriberSpec extends ObjectBehavior
{
    function let(ProductDraftRepositoryInterface $productDraftRepo, BulkRemoverInterface $remover)
    {
        $this->beConstructedWith($productDraftRepo, $remover);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_product_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_REMOVE      => 'removeDraftsByProduct',
                ProductEvents::POST_MASS_REMOVE => 'removeDraftsByProducts'
            ]
        );
    }

    function it_removed_drafts_for_a_product(
        $productDraftRepo,
        $remover,
        RemoveEvent $event,
        ProductInterface $product,
        ProductDraftInterface $draftMary,
        ProductDraftInterface $draftSandra
    ) {
        $product->getId()->willReturn('568bf91fb392ea7a648b4567');
        $event->getSubject()->willReturn($product);

        $drafts = [$draftMary, $draftSandra];
        $productDraftRepo->findBy(['product.$id' => new \MongoId('568bf91fb392ea7a648b4567')])->willReturn($drafts);
        $remover->removeAll($drafts)->shouldBeCalled();

        $this->removeDraftsByProduct($event);
    }

    function it_removed_drafts_for_a_product_on_many_products(
        $productDraftRepo,
        $remover,
        RemoveEvent $event,
        ProductDraftInterface $draftMary,
        ProductDraftInterface $draftSandra
    ) {
        $event->getSubject()->willReturn(['568bf91fb392ea7a648b4567', '468bf91fb392ea7a648b4567']);

        $drafts = [$draftMary, $draftSandra];
        $productDraftRepo->findBy(['product.$id' => new \MongoId('568bf91fb392ea7a648b4567')])->willReturn($drafts);
        $productDraftRepo->findBy(['product.$id' => new \MongoId('468bf91fb392ea7a648b4567')])->willReturn(null);
        $remover->removeAll($drafts)->shouldBeCalled();

        $this->removeDraftsByProducts($event);
    }
}
