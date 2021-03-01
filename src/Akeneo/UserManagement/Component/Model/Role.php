<?php

namespace Akeneo\UserManagement\Component\Model;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Role implements RoleInterface
{
    protected ?int $id = null;
    protected ?string $role = null;
    protected ?string $label = null;

    public function __construct(?string $role = null)
    {
        $this->role = $role;
        $this->label = '';
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
    public function setRole(?string $role): void
    {
        $this->role = $role;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
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
