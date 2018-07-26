<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Comment\Repository\CommentRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Comment repository
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentRepository extends EntityRepository implements CommentRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getComments($resourceName, $resourceId)
    {
        return $this->findBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'parent' => null],
            ['createdAt'  => 'desc']
        );
    }
}
