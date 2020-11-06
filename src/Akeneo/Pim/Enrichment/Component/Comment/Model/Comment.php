<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Model;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Comment model
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Comment implements CommentInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $resourceName;

    /** @var string */
    protected $resourceId;

    /** @var UserInterface */
    protected $author;

    /** @var string */
    protected $body;

    /** @var \DateTime */
    protected $createdAt;

    /** @var \DateTime */
    protected $repliedAt;

    /** @var CommentInterface */
    protected $parent;

    /** @var ArrayCollection[] */
    protected $children;

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceId(string $resourceId): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceName(string $resourceName): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->resourceName = $resourceName;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor(UserInterface $author): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): \Akeneo\UserManagement\Component\Model\UserInterface
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody(string $body): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->body = $body;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(CommentInterface $parent): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setRepliedAt(\DateTime $repliedAt): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->repliedAt = $repliedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepliedAt(): \DateTime
    {
        return $this->repliedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): ArrayCollection
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren(ArrayCollection $children): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
    {
        $this->children = $children;

        return $this;
    }
}
