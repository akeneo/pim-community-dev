<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Component\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Channel variations configuration repository interface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
interface ChannelConfigurationRepositoryInterface extends ObjectRepository, IdentifiableObjectRepositoryInterface
{
}
