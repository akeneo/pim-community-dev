<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\ImportExport;

use Akeneo\Component\StorageUtils\StorageEvents;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddAttributeGroupPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        AttributeGroupAccessManager $accessManager,
        GroupRepository $groupRepository
    ) {
        $this->beConstructedWith($accessManager, $groupRepository);
    }

    function it_subscribes_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::PRE_SAVE_ALL => 'storeNewAttributeGroupCodes',
                StorageEvents::POST_SAVE_ALL => 'setDefaultPermissions'
            ]
        );
    }

    function it_stores_new_attribute_group_code(
        GenericEvent $event,
        AttributeGroupInterface $attributeGroup
    ) {
        $event->getSubject()->willReturn([$attributeGroup]);
        $attributeGroup->getId()->willReturn(null);
        $attributeGroup->getCode()->shouldBeCalled();

        $this->storeNewAttributeGroupCodes($event);
    }

    function it_does_not_store_existing_attribute_code(
        GenericEvent $event,
        AttributeGroupInterface $attributeGroup
    ) {
        $event->getSubject()->willReturn($attributeGroup);
        $attributeGroup->getId()->willReturn(42);
        $attributeGroup->getCode()->shouldNotBeCalled();

        $this->storeNewAttributeGroupCodes($event);
    }

    function it_set_default_permissions(
        $accessManager,
        $groupRepository,
        Group $defaultGroup,
        GenericEvent $event,
        AttributeGroupInterface $attributeGroup
    ) {
        $event->getSubject()->willReturn([$attributeGroup]);
        $attributeGroup->getId()->willReturn(null);
        $attributeGroup->getCode()->willReturn('attribute_group_code');

        $this->storeNewAttributeGroupCodes($event);

        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);
        $accessManager->setAccess($attributeGroup, [$defaultGroup], [$defaultGroup])->shouldBeCalled();

        $this->setDefaultPermissions($event);
    }
}
