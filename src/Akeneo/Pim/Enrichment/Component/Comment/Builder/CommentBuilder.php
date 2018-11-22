<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Builder;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentSubjectInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Comment builder
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentBuilder
{
    /** @var string */
    protected $className;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * @return CommentInterface
     */
    public function newInstance()
    {
        return new $this->className();
    }

    /**
     * @param CommentSubjectInterface $subject
     * @param UserInterface           $user
     *
     * @return CommentInterface
     */
    public function buildComment(CommentSubjectInterface $subject, UserInterface $user)
    {
        $now = new \DateTime();

        /** @var CommentInterface $comment */
        $comment = new $this->className();
        $comment->setResourceName(ClassUtils::getClass($subject));
        $comment->setResourceId($subject->getId());
        $comment->setAuthor($user);
        $comment->setCreatedAt($now);
        $comment->setRepliedAt($now);
        $comment->setChildren(new ArrayCollection());

        return $comment;
    }

    /**
     * @param UserInterface $user
     *
     * @return CommentInterface
     */
    public function buildCommentWithoutSubject(UserInterface $user)
    {
        $now = new \DateTime();

        /** @var CommentInterface $comment */
        $comment = new $this->className();
        $comment->setAuthor($user);
        $comment->setCreatedAt($now);
        $comment->setRepliedAt($now);
        $comment->setChildren(new ArrayCollection());

        return $comment;
    }

    /**
     * @param CommentInterface $comment
     * @param UserInterface    $user
     *
     * @return CommentInterface
     */
    public function buildReply(CommentInterface $comment, UserInterface $user)
    {
        $now = new \DateTime();

        /** @var CommentInterface $reply */
        $reply = new $this->className();
        $reply->setResourceName($comment->getResourceName());
        $reply->setResourceId($comment->getResourceId());
        $reply->setAuthor($user);
        $reply->setCreatedAt($now);
        $reply->setRepliedAt($now);
        $reply->setParent($comment);
        $comment->setRepliedAt($now);

        return $reply;
    }
}
