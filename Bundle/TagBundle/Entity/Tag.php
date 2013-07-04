<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FPN\TagBundle\Entity\Tag as BaseTag;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * Tag
 *
 * @ORM\Table(name="oro_tag_tag")
 * @ORM\Entity
 */
class Tag extends BaseTag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Tagging", mappedBy="tag", fetch="EAGER")
     */
    protected $tagging;

    /**
     * @var User
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(name="user_tags",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $users;

    /**
     * Return tag owner user or null if it's global scope
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }
}
