<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ProductAsset\Repository\ChannelConfigurationRepositoryInterface;

/**
 * Channel variations configuration repository
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ChannelConfigurationRepository extends EntityRepository implements ChannelConfigurationRepositoryInterface
{
}
