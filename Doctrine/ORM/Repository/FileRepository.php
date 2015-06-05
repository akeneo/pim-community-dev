<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\FileStorageBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\ProductAsset\Repository\FileRepositoryInterface;

/**
 * Implementation of ReferenceRepositoryInterface
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class FileRepository extends EntityRepository implements FileRepositoryInterface
{
}
