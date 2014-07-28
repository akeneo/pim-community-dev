<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;

class AddCategoryPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich\AddCategoryPermissionsSubscriber');
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            CategoryEvents::POST_CREATE => 'addNewCategoryPermissions'
        ]);
    }

    function it_adds_parent_permissions_to_new_category(GenericEvent $event, CategoryInterface $category, CategoryInterface $parent, CategoryAccessManager $accessManager)
    {
        $event->getSubject()->willReturn($category);
        $category->getParent()->willReturn($parent);
        $accessManager->getViewUserGroups($parent)->willReturn(['A', 'B']);
        $accessManager->getEditUserGroups($parent)->willReturn(['C']);
        $accessManager->getOwnUserGroups($parent)->willReturn(['C']);
        $accessManager->setAccess($category, ['A', 'B'], ['C'], ['C'])->shouldBeCalled();
        $this->addNewCategoryPermissions($event);
    }
}
