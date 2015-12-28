<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber;

use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class AbstractProposalSubscriber
 *
 * This abstract subscriber listens events related to proposals notifications. It contains generic functions to
 * manage the notified users of updated/created product drafts.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
abstract class AbstractProposalSubscriber implements EventSubscriberInterface
{
    /** @var NotificationManager */
    protected $notificationManager;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param NotificationManager      $notificationManager
     * @param CategoryAccessRepository $categoryAccessRepo
     * @param UserRepositoryInterface  $userRepository
     */
    public function __construct(
        NotificationManager $notificationManager,
        CategoryAccessRepository $categoryAccessRepo,
        UserRepositoryInterface $userRepository
    ) {
        $this->notificationManager = $notificationManager;
        $this->categoryAccessRepo  = $categoryAccessRepo;
        $this->userRepository      = $userRepository;
    }

    /**
     * Filter a set of users according to their profile notifications options
     *
     * @param UserInterface[] $users
     *
     * @return UserInterface[]
     */
    protected function getUsersToNotify(array $users)
    {
        $usersToNotify = [];
        foreach ($users as $user) {
            if ($user->hasProposalsToReviewNotification()) {
                $usersToNotify[] = $user;
            }
        }

        return $usersToNotify;
    }

    /**
     * Return the set of group ids owner of a product.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOwnerGroupIds(ProductInterface $product)
    {
        $ownerGroupsId = [];
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForProduct($product, Attributes::OWN_PRODUCTS);
        foreach ($ownerGroups as $userGroup) {
            $ownerGroupsId[] = $userGroup['id'];
        }

        return $ownerGroupsId;
    }
}
