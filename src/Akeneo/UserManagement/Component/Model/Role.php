<?php

namespace Akeneo\UserManagement\Component\Model;

use Symfony\Component\Security\Core\Role\Role as SymfonyRole;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @todo This "write" model should not extend Symfony\Component\Security\Core\Role\Role.We should create a "read"
 * model that extends that class.
 *
 * For now, this model MUST extend Symfony\Component\Security\Core\Role\Role because the symfony security component
 * does some stuff if the role is a instance of this class. You should have a look to
 * Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity for instance
 */
class Role extends SymfonyRole implements RoleInterface
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
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setRole($role): void
    {
        $this->role = (string) strtoupper($role);

        // every role should be prefixed with 'ROLE_'
        if (strpos($this->role, 'ROLE_') !== 0 && User::ROLE_ANONYMOUS !== $role) {
            $this->role = 'ROLE_' . $this->role;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label): void
    {
        $this->label = (string) $label;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->role;
    }
}
