<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Component\Catalog\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddCategoryPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager, 'Pim\Component\Catalog\Model\CategoryInterface', true);
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([CategoryEvents::POST_CREATE => 'addNewCategoryPermissions']);
    }

    function it_adds_parent_permissions_to_new_category(
        GenericEvent $event,
        CategoryInterface $category,
        CategoryAccessManager $accessManager
    ) {
        $event->getSubject()->willReturn($category);
        $accessManager->setAccessLikeParent($category, ['owner' => true])->shouldBeCalled();
        $this->addNewCategoryPermissions($event);
    }
}
