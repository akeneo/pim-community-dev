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
     * Get comments related to a resource, ordered by replied date.
     * Replied date is set to creation date if there was no reply.
     * Replies are not returned by this method.
     *
     * @param $resourceName
     * @param $resourceId
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|\Pim\Bundle\CommentBundle\Model\CommentInterface[]
     */
    public function getComments($resourceName, $resourceId);
}
