<?php

namespace Pim\Bundle\UserBundle\Entity;

/**
 * Class UserApi
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserApi
{
    /** @var int */
    protected $id;

    /** @var UserInterface */
    protected $user;

    /** @var string */
    protected $apiKey;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
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
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
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
