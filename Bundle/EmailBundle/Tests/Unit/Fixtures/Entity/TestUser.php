<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Symfony\Component\Security\Core\User\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class TestUser implements UserInterface, EmailOwnerInterface
{
    private $email;

    private $firstName;

    private $lastName;

    public function __construct($email = null, $firstName = null, $lastName = null)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFirstname()
    {
        return $this->firstName;
    }

    public function getLastname()
    {
        return $this->lastName;
    }

    public function getClass()
    {
    }

    public function getPrimaryEmailField()
    {
    }

    public function getId()
    {
    }

    public function getFullname($format = '')
    {
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
    }

    public function eraseCredentials()
    {
    }
}
