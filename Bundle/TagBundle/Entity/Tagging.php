<?php

namespace Oro\Bundle\TagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FPN\TagBundle\Entity\Tagging as BaseTagging;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Table(
 *     name="oro_tag_tagging",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="tagging_idx", columns={"tag_id", "resource_type", "resource_id", "user_id"})}
 * )
 * @ORM\Entity
 */
class Tagging extends BaseTagging
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="tagging")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $tag;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", cascade="remove")
     */
    protected $user;

    /**
     * Return tag relation owner user
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
