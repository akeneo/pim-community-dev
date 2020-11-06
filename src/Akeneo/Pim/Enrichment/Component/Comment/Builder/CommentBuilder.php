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
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function newInstance(): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        return new $this->className();
    }

    /**
     * @param CommentSubjectInterface $subject
     * @param UserInterface           $user
     */
    public function buildComment(CommentSubjectInterface $subject, UserInterface $user): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
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
     */
    public function buildCommentWithoutSubject(UserInterface $user): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
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
     */
    public function buildReply(CommentInterface $comment, UserInterface $user): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
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
