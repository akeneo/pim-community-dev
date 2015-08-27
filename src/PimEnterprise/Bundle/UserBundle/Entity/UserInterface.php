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
     */
    public function setAssetDelayReminder($assetDelayReminder);
}
