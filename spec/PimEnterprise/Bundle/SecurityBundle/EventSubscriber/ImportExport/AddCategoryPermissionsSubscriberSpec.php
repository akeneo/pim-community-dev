<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
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
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::PRE_SAVE_ALL => 'storeNewCategoryCodes',
                StorageEvents::POST_SAVE_ALL => 'copyParentPermissions'
            ]
        );
    }

    function it_adds_parent_permissions_to_new_category(
        GenericEvent $event,
        CategoryInterface $category,
        $accessManager
    ) {
        $event->getSubject()->willReturn([$category]);
        $category->getId()->willReturn(null);
        $category->getCode()->willReturn('new_category');
        $accessManager->setAccessLikeParent($category, ['owner' => true])->shouldBeCalled();
        $this->storeNewCategoryCodes($event);
        $this->copyParentPermissions($event);
    }
}
