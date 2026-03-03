<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\UserGroup\GetUserGroupsWithDefaultPermission;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddDefaultPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager,
        CategoryAccessManager $productCategoryAccessManager,
        LocaleAccessManager $localeAccessManager,
        GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission
    ) {
        $this->beConstructedWith(
            $groupRepository,
            $attributeGroupAccessManager,
            $jobInstanceAccessManager,
            $productCategoryAccessManager,
            $localeAccessManager,
            $getUserGroupsWithDefaultPermission
        );
    }

    function it_subscribes_to_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                StorageEvents::POST_SAVE => 'setDefaultPermissions',
            ]
        );
    }

    function it_set_default_permissions_on_new_attribute_groups(
        $groupRepository,
        $attributeGroupAccessManager,
        GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission,
        Group $defaultGroup
    ) {
        $attributeGroup = new AttributeGroup();
        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);
        $getUserGroupsWithDefaultPermission->execute('attribute_group_view')->willReturn([]);
        $getUserGroupsWithDefaultPermission->execute('attribute_group_edit')->willReturn([]);
        $attributeGroupAccessManager->setAccess($attributeGroup, [$defaultGroup], [$defaultGroup])->shouldBeCalled();

        $this->setDefaultPermissions(new GenericEvent($attributeGroup, ['is_new' => true]));
    }

    function it_set_default_permissions_on_new_job_instances(
        $groupRepository,
        $jobInstanceAccessManager,
        Group $defaultGroup
    ) {
        $jobInstance = new JobInstance();
        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);
        $jobInstanceAccessManager->setAccess($jobInstance, [$defaultGroup], [$defaultGroup])->shouldBeCalled();

        $this->setDefaultPermissions(new GenericEvent($jobInstance, ['is_new' => true]));
    }

    function it_does_not_set_permissions_on_other_new_entities(
        $attributeGroupAccessManager,
        $jobInstanceAccessManager
    ) {
        $attribute = new Attribute();
        $attributeGroupAccessManager->setAccess(Argument::cetera())->shouldNotBeCalled();
        $jobInstanceAccessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->setDefaultPermissions(new GenericEvent($attribute, ['is_new' => true]));
    }

    function it_does_not_set_permissions_on_already_existing_entities(
        $attributeGroupAccessManager,
        $jobInstanceAccessManager
    ) {
        $attributeGroup = new AttributeGroup();
        $attributeGroupAccessManager->setAccess(Argument::cetera())->shouldNotBeCalled();
        $jobInstanceAccessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->setDefaultPermissions(new GenericEvent($attributeGroup, ['is_new' => false]));
    }

    function it_set_default_permissions_on_root_product_category(
        $groupRepository,
        $productCategoryAccessManager,
        GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission,
        Group $defaultGroup,
        CategoryInterface $category,
        GenericEvent $event
    ) {
        $event->hasArgument('is_new')->willReturn(true);
        $event->getArgument('is_new')->willReturn(true);

        $event->hasArgument('is_installation')->willReturn(false);

        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(true);

        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);

        $getUserGroupsWithDefaultPermission->execute('category_view')->willReturn([]);
        $getUserGroupsWithDefaultPermission->execute('category_edit')->willReturn([]);
        $getUserGroupsWithDefaultPermission->execute('category_own')->willReturn([]);

        $productCategoryAccessManager->setAccess(
            $category,
            [],
            [],
            [$defaultGroup]
        )->shouldBeCalled();

        $this->setDefaultPermissions($event);
    }

    function it_set_default_permissions_on_product_category(
        $groupRepository,
        $productCategoryAccessManager,
        Group $defaultGroup,
        CategoryInterface $category,
        GenericEvent $event
    ) {
        $event->hasArgument('is_new')->willReturn(true);
        $event->getArgument('is_new')->willReturn(true);

        $event->hasArgument('is_installation')->willReturn(false);

        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(false);

        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);

        $productCategoryAccessManager->setAccessLikeParent(
            $category,
            ['owner' => true]
        )->shouldBeCalled();

        $this->setDefaultPermissions($event);
    }

    function it_sets_default_permissions_on_new_locale(
        $groupRepository,
        $localeAccessManager,
        GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission,
        Group $defaultGroup
    ) {
        $locale = new Locale();
        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);
        $getUserGroupsWithDefaultPermission->execute('locale_view')->willReturn([]);
        $getUserGroupsWithDefaultPermission->execute('locale_edit')->willReturn([]);
        $localeAccessManager->setAccess($locale, [], [])->shouldBeCalled();

        $this->setDefaultPermissions(new GenericEvent($locale, ['is_new' => true]));
    }

    function it_sets_default_permissions_on_updated_locale(
        $groupRepository,
        $localeAccessManager,
        GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission,
        Group $defaultGroup
    ) {
        $locale = new Locale();
        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);
        $getUserGroupsWithDefaultPermission->execute('locale_view')->willReturn([]);
        $getUserGroupsWithDefaultPermission->execute('locale_edit')->willReturn([]);
        $localeAccessManager->setAccess($locale, [], [])->shouldBeCalled();

        $this->setDefaultPermissions(new GenericEvent($locale, ['is_new' => false]));
    }
}
