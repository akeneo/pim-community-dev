<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\EnrichBundle\Event\CategoryEvents;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddDefaultUserGroupSubscriberSpec extends ObjectBehavior
{
    function let(GroupRepository $groupRepository, CategoryAccessManager $catAccessManager)
    {
        $this->beConstructedWith($groupRepository, $catAccessManager);
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                CategoryEvents::POST_CREATE => 'addDefaultUserGroup'
            ]
        );
    }

    function it_grants_access_to_the_tree_for_default_user_group(
        $groupRepository,
        $catAccessManager,
        GenericEvent $event,
        CategoryInterface $category,
        Group $userGroup
    ) {
        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(true);

        $groupRepository->getDefaultUserGroup()->willReturn($userGroup);
        $catAccessManager->grantAccess($category, $userGroup, Attributes::EDIT_PRODUCTS)->shouldBeCalled();

        $this->addDefaultUserGroup($event)->shouldReturn(null);
    }

    function it_does_not_grant_anything_on_a_non_category(GenericEvent $event)
    {
        $event->getSubject()->willReturn(null);

        $this->addDefaultUserGroup($event)->shouldReturn(null);
    }

    function it_does_not_grant_access_if_the_category_is_not_a_tree(
        GenericEvent $event,
        CategoryInterface $category
    ) {
        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(false);

        $this->addDefaultUserGroup($event)->shouldReturn(null);
    }
}
