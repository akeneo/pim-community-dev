<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\PublishedProduct;

use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

class CheckPublishedProductOnRemovalSubscriberSpec extends ObjectBehavior
{
    function let(PublishedProductRepositoryInterface $publishedRepository)
    {
        $this->beConstructedWith($publishedRepository);
    }

    function it_subscribes_to_pre_remove_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            CatalogEvents::PRE_REMOVE_PRODUCT => 'checkProductHasBeenPublished',
            CatalogEvents::PRE_REMOVE_FAMILY  => 'checkFamilyLinkedToPublishedProduct'
        ]);
    }

    function it_checks_if_a_product_is_not_published(
        $publishedRepository,
        AbstractProduct $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $publishedRepository->findOneByOriginalProductId(1)->willReturn(false);

        $this->checkProductHasBeenPublished($event);
    }

    function it_throws_an_exception_if_the_product_is_published(
        $publishedRepository,
        AbstractProduct $product,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($product);
        $product->getId()->willReturn(1);
        $publishedRepository->findOneByOriginalProductId(1)->willReturn(true);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove a published product'))
            ->during('checkProductHasBeenPublished', [$event]);
    }

    function it_checks_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        Family $family,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductForFamily($family)->willReturn(0);

        $this->checkFamilyLinkedToPublishedProduct($event);
    }

    function it_throws_an_exception_if_the_family_is_linked_to_a_published_product(
        $publishedRepository,
        Family $family,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn($family);
        $publishedRepository->countPublishedProductForFamily($family)->willReturn(1);

        $this
            ->shouldThrow(new ConflictHttpException('Impossible to remove family linked to a published product'))
            ->during('checkFamilyLinkedToPublishedProduct', [$event]);
    }
}
