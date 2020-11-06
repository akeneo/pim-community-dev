<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Model;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Comment model interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CommentInterface
{
    public function getId(): int;

    /**
     * @param string $resourceId
     */
    public function setResourceId(string $resourceId): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getResourceId(): string;

    /**
     * @param string $resourceName
     */
    public function setResourceName(string $resourceName): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getResourceName(): string;

    /**
     * @param UserInterface $author
     */
    public function setAuthor(UserInterface $author): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getAuthor(): \Akeneo\UserManagement\Component\Model\UserInterface;

    /**
     * @param string $body
     */
    public function setBody(string $body): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getBody(): string;

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getCreatedAt(): \DateTime;

    /**
     * @param CommentInterface $parent
     */
    public function setParent(CommentInterface $parent): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getParent(): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    /**
     * @param \DateTime $repliedAt
     */
    public function setRepliedAt(\DateTime $repliedAt): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;

    public function getRepliedAt(): \DateTime;

    public function getChildren(): \Doctrine\Common\Collections\ArrayCollection;

    /**
     * @param ArrayCollection $children
     */
    public function setChildren(ArrayCollection $children): \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
}
