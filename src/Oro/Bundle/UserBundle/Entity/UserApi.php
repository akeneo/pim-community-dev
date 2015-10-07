<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * @ORM\Table(name="oro_user_api")
 * @ORM\Entity
 */
class UserApi
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var UserInterface
     *
     * @ORM\OneToOne(targetEntity="\Pim\Bundle\UserBundle\Entity\UserInterface", inversedBy="api", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", unique=true, length=255, nullable=false)
     */
    protected $apiKey;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return UserApi
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     *
     * @return UserApi
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Generate random API key
     *
     * @return string
     */
    public function generateKey()
    {
        return bin2hex(hash('sha1', uniqid(mt_rand(), true), true));
    }
}
