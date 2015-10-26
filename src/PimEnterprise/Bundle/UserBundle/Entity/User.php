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

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\UserBundle\Entity\User as BaseUser;
use PimEnterprise\Component\ProductAsset\Model\CategoryInterface;

/**
 * Enterprise override of the Community user
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class User extends BaseUser implements UserInterface
{
    /** @var int The delay in days to send an email before the expiration of an asset */
    protected $assetDelayReminder = 5;

     /** @var CategoryInterface */
    protected $defaultAssetTree;

    /** @var bool Be notified when the user receives a proposal to review */
    protected $proposalsToReviewNotification;

    /** @var bool Be notified when the user's proposal has been accepted or rejected */
    protected $proposalsStateNotification;

    /**
     * {@inheritdoc}
     */
    public function getAssetDelayReminder()
    {
        return $this->assetDelayReminder;
    }

    /**
     * {@inheritdoc}
     */
    public function setAssetDelayReminder($assetDelayReminder)
    {
        $this->assetDelayReminder = (int) $assetDelayReminder;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultAssetTree()
    {
        return $this->defaultAssetTree;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultAssetTree(CategoryInterface $defaultAssetTree)
    {
        $this->defaultAssetTree = $defaultAssetTree;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProposalsToReviewNotification()
    {
        return $this->proposalsToReviewNotification;
    }

    /**
     * {@inheritdoc}
     */
    public function setProposalsToReviewNotification($proposalsToReviewNotification)
    {
        $this->proposalsToReviewNotification = $proposalsToReviewNotification;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProposalsStateNotification()
    {
        return $this->proposalsStateNotification;
    }

    /**
     * {@inheritdoc}
     */
    public function setProposalsStateNotification($proposalsStateNotification)
    {
        $this->proposalsStateNotification = $proposalsStateNotification;

        return $this;
    }
}
