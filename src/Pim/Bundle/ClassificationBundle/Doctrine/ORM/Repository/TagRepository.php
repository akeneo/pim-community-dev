<?php

namespace Pim\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Classification\Repository\TagRepositoryInterface;

/**
 * Tag repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TagRepository extends EntityRepository implements TagRepositoryInterface
{
}
