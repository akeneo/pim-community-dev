<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Entity;

use Pim\Bundle\UserBundle\Entity\UserInterface as BaseUserInterface;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Interface UserInterface
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface UserInterface extends BaseUserInterface
{
    /**
     * @return int
     */
    public function getAssetDelayReminder();

    /**
     * Set delay
     *
     * @param int $assetDelayReminder
     *
     * @return UserInterface
     */
    public function setAssetDelayReminder($assetDelayReminder);

    /**
     * @return CategoryInterface
     */
    public function getDefaultAssetTree();

    /**
     * @param CategoryInterface $defaultAssetTree
     *
     * @return UserInterface
     */
    public function setDefaultAssetTree(CategoryInterface $defaultAssetTree);

    /**
     * Does the user want to be notified of new proposals to review ?
     *
     * @return bool
     */
    public function hasProposalsToReviewNotification();

    /**
     * Set whether the user wants to be notified of new proposals.
     *
     * @param bool $proposalsToReviewNotification
     *
     * @return UserInterface
     */
    public function setProposalsToReviewNotification($proposalsToReviewNotification);

    /**
     * Does the user want to be notified when its proposals are accepted or rejected ?
     *
     * @return bool
     */
    public function hasProposalsStateNotification();

    /**
     * Set whether the user wants to be notified when its proposals are accepted or rejected.
     *
     * @param bool $proposalsStateNotification
     *
     * @return UserInterface
     */
    public function setProposalsStateNotification($proposalsStateNotification);
}
