<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Bundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Akeneo\Asset\Component\Repository\ReferenceRepositoryInterface;

/**
 * Implementation of ReferenceRepositoryInterface
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ReferenceRepository extends EntityRepository implements ReferenceRepositoryInterface
{
}
