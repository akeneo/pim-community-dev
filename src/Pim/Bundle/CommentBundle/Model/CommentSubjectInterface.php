<?php

namespace Pim\Bundle\CommentBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Comment subject interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CommentSubjectInterface
{
    /**
     * @return string|int
     */
    public function getId();

    /**
     * @param CommentInterface $comment
     *
     * @return CommentSubjectInterface
     */
    //public function hasComment(CommentInterface $comment);

    /**
     * @param CommentInterface $comment
     *
     * @return CommentSubjectInterface
     */
    //public function addComment(CommentInterface $comment);

    /**
     * @param CommentInterface $comment
     *
     * @return CommentSubjectInterface
     */
    //public function removeComment(CommentInterface $comment);

    /**
     * @param ArrayCollection $comments
     *
     * @return CommentSubjectInterface
     */
    //public function setComments(ArrayCollection $comments);

    /**
     * @return ArrayCollection|CommentInterface[]
     */
    //public function getComments();
}
