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
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface as BaseUserInterface;

/**
 * Enterprise override of the Community user
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class User extends BaseUser implements BaseUserInterface
{
    /**
     * The delay in day to send an email before the expiration of an asset
     *
     * @var int
     */
    protected $assetDelayReminder = 5;

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
        $this->assetDelayReminder = $assetDelayReminder;

        return $this;
    }
}
