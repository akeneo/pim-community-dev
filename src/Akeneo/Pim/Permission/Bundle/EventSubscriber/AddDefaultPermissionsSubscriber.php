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

namespace Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Asset\Component\Model\CategoryInterface as ProductAssetCategoryInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber responsible for setting default permissions on creation for attribute groups, job instances,
 * product categories, product asset categories and locales.
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

    /** @var LocaleAccessManager */
    private $localeAccessManager;

    public function __construct(
        GroupRepository $groupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager,
        CategoryAccessManager $productCategoryAccessManager,
        CategoryAccessManager $productAssetCategoryAccessManager,
        LocaleAccessManager $localeAccessManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->jobInstanceAccessManager = $jobInstanceAccessManager;
        $this->productCategoryAccessManager = $productCategoryAccessManager;
        $this->productAssetCategoryAccessManager = $productAssetCategoryAccessManager;
        $this->localeAccessManager = $localeAccessManager;
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

        if ($event->hasArgument('is_installation') && $event->getArgument('is_installation')) {
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
        } elseif ($subject instanceof LocaleInterface) {
            $this->localeAccessManager->setAccess($subject, [$defaultGroup], [$defaultGroup]);
        }
    }
}
