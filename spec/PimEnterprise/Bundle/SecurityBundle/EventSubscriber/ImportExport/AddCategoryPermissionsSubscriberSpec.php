<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events as DoctrineEvents;

class AddCategoryPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            DoctrineEvents::prePersist
        ]);
    }

    function it_adds_parent_permissions_to_new_category(LifecycleEventArgs $event, CategoryInterface $category, CategoryAccessManager $accessManager)
    {
        $event->getEntity()->willReturn($category);
        $accessManager->setAccessLikeParent($category)->shouldBeCalled();
        $this->prePersist($event);
    }
}
