<?php

namespace Pim\Bundle\CommentBundle\Repository;

/**
 * Comment repository interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CommentRepositoryInterface
{
    /**
     * Get comments related to a resource, ordered by creation date.
     * Replies are not returned by this method.
     *
     * @param string         $resourceName
     * @param integer|string $resourceId
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Pim\Bundle\CommentBundle\Model\CommentInterface[]
     */
    public function getComments($resourceName, $resourceId);
}
