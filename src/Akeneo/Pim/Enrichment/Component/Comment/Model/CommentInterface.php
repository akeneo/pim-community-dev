<?php

namespace Akeneo\Pim\Enrichment\Component\Comment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

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
    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $resourceId
     *
     * @return CommentInterface
     */
    public function setResourceId($resourceId);

    /**
     * @return string
     */
    public function getResourceId();

    /**
     * @param string $resourceName
     *
     * @return CommentInterface
     */
    public function setResourceName($resourceName);

    /**
     * @return string
     */
    public function getResourceName();

    /**
     * @param UserInterface $author
     *
     * @return CommentInterface
     */
    public function setAuthor(UserInterface $author);

    /**
     * @return UserInterface
     */
    public function getAuthor();

    /**
     * @param string $body
     *
     * @return CommentInterface
     */
    public function setBody($body);

    /**
     * @return string
     */
    public function getBody();

    /**
     * @param \DateTime $createdAt
     *
     * @return CommentInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param CommentInterface $parent
     *
     * @return CommentInterface
     */
    public function setParent(CommentInterface $parent);

    /**
     * @return CommentInterface
     */
    public function getParent();

    /**
     * @param \DateTime $repliedAt
     *
     * @return CommentInterface
     */
    public function setRepliedAt(\DateTime $repliedAt);

    /**
     * @return \DateTime
     */
    public function getRepliedAt();

    /**
     * @return ArrayCollection
     */
    public function getChildren();

    /**
     * @param ArrayCollection $children
     *
     * @return CommentInterface
     */
    public function setChildren(ArrayCollection $children);
}
