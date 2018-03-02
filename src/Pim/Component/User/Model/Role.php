<?php

namespace Pim\Component\User\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use Pim\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Role\Role as BaseRole;

class Role extends BaseRole
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $role;

    /** @var string */
    protected $label;

    /**
     * Populate the role field
     *
     * @param string $role ROLE_FOO etc
     */
    public function __construct($role = '')
    {
        $this->role = $role;
        $this->label = $role;
    }

    /**
     * Return the role id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Return the role label field
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set role name only for newly created role
     *
     * @param  string            $role Role name
     * @throws \RuntimeException
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = (string) strtoupper($role);

        // every role should be prefixed with 'ROLE_'
        if (strpos($this->role, 'ROLE_') !== 0 && User::ROLE_ANONYMOUS !== $role) {
            $this->role = 'ROLE_' . $this->role;
        }

        return $this;
    }

    /**
     * Set the new label for role
     *
     * @param  string $label New label
     * @return Role
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;

        return $this;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->role;
    }
}
