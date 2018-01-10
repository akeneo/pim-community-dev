<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Doctrine\ORM\Repository\GroupRepository;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface as ProductAssetCategoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber responsible for setting default permissions on creation for attribute groups, job instances,
 * product categories and product asset categories.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class AddDefaultPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var GroupRepository */
    private $groupRepository;

    /** @var AttributeGroupAccessManager */
    private $attributeGroupAccessManager;

    /** @var JobProfileAccessManager */
    private $jobInstanceAccessManager;

    /** @var CategoryAccessManager */
    private $productCategoryAccessManager;

    /** @var CategoryAccessManager */
    private $productAssetCategoryAccessManager;

    /**
     * @param GroupRepository             $groupRepository
     * @param AttributeGroupAccessManager $attributeGroupAccessManager
     * @param JobProfileAccessManager     $jobInstanceAccessManager
     * @param CategoryAccessManager       $productCategoryAccessManager
     * @param CategoryAccessManager       $productAssetCategoryAccessManager
     */
    public function __construct(
        GroupRepository $groupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager,
        CategoryAccessManager $productCategoryAccessManager,
        CategoryAccessManager $productAssetCategoryAccessManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->jobInstanceAccessManager = $jobInstanceAccessManager;
        $this->productCategoryAccessManager = $productCategoryAccessManager;
        $this->productAssetCategoryAccessManager = $productAssetCategoryAccessManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'setDefaultPermissions'
        ];
    }

    /**
     * Set the default permissions to the new job instance.
     *
     * @param GenericEvent $event
     */
    public function setDefaultPermissions(GenericEvent $event): void
    {
        if (!$event->hasArgument('is_new') || !$event->getArgument('is_new')) {
            return;
        }

        $subject = $event->getSubject();
        $defaultGroup = $this->groupRepository->getDefaultUserGroup();

        if (null === $defaultGroup) {
            return;
        }

        if ($subject instanceof AttributeGroupInterface) {
            $this->attributeGroupAccessManager->setAccess($subject, [$defaultGroup], [$defaultGroup]);
        } elseif ($subject instanceof JobInstance) {
            $this->jobInstanceAccessManager->setAccess($subject, [$defaultGroup], [$defaultGroup]);
        } elseif ($subject instanceof CategoryInterface && $subject->isRoot()) {
            $this->productCategoryAccessManager->grantAccess($subject, $defaultGroup, Attributes::OWN_PRODUCTS);
        } elseif ($subject instanceof CategoryInterface) {
            $this->productCategoryAccessManager->setAccessLikeParent($subject, ['owner' => true]);
        } elseif ($subject instanceof ProductAssetCategoryInterface) {
            $this->productAssetCategoryAccessManager->setAccessLikeParent($subject, ['owner' => false]);
        }
    }
}
