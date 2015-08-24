<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events as DoctrineEvents;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

class AddCategoryPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([DoctrineEvents::prePersist]);
    }

    function it_adds_parent_permissions_to_new_category(
        LifecycleEventArgs $event,
        CategoryInterface $category,
        $accessManager
    ) {
        $event->getEntity()->willReturn($category);
        $accessManager->setAccessLikeParent($category, ['owner' => false], false)->shouldBeCalled();
        $this->prePersist($event);
    }
}
