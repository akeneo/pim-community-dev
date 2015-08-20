<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\UserBundle\Entity\User as BaseUser;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Enterprise override of the Community user
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class User extends BaseUser
{
    /**
     * The delay in day to send an email before the expiration of an asset
     *
     * @var int
     */
    protected $assetDelayReminder = 5;

    /**
     * @return int
     */
    public function getAssetDelayReminder()
    {
        return $this->assetDelayReminder;
    }

    /**
     * @param int $assetDelayReminder
     *
     * @return UserInterface
     */
    public function setAssetDelayReminder($assetDelayReminder)
    {
        $this->assetDelayReminder = $assetDelayReminder;

        return $this;
    }
}
