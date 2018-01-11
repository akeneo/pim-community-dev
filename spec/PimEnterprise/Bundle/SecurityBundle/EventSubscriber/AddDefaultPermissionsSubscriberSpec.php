<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\StorageEvents;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Component\Catalog\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface as ProductAssetCategoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddDefaultPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        GroupRepository $groupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager,
        CategoryAccessManager $productCategoryAccessManager,
        CategoryAccessManager $assetCategoryAccessManager
    ) {
        $this->beConstructedWith(
            $groupRepository,
            $attributeGroupAccessManager,
            $jobInstanceAccessManager,
            $productCategoryAccessManager,
            $assetCategoryAccessManager
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
        Group $defaultGroup
    ) {
        $attributeGroup = new AttributeGroup();
        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);
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
        Group $defaultGroup,
        CategoryInterface $category,
        GenericEvent $event
    ) {
        $event->hasArgument('is_new')->willReturn(true);
        $event->getArgument('is_new')->willReturn(true);

        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(true);

        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);

        $productCategoryAccessManager->grantAccess(
            $category,
            $defaultGroup,
            Attributes::OWN_PRODUCTS
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

        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(false);

        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);

        $productCategoryAccessManager->setAccessLikeParent(
            $category,
            ['owner' => true]
        )->shouldBeCalled();

        $this->setDefaultPermissions($event);
    }

    function it_set_default_permissions_on_product_asset_category(
        $groupRepository,
        $assetCategoryAccessManager,
        Group $defaultGroup,
        ProductAssetCategoryInterface $category,
        GenericEvent $event
    ) {
        $event->hasArgument('is_new')->willReturn(true);
        $event->getArgument('is_new')->willReturn(true);

        $event->getSubject()->willReturn($category);
        $category->isRoot()->willReturn(false);

        $groupRepository->getDefaultUserGroup()->willReturn($defaultGroup);

        $assetCategoryAccessManager->setAccessLikeParent(
            $category,
            ['owner' => false]
        )->shouldBeCalled();

        $this->setDefaultPermissions($event);
    }
}
