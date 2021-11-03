<?php

namespace Akeneo\UserManagement\Component\Model;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface RoleInterface
{
    /**
     * Return the role id
     *
     * @return int
     */
    public function getId(): ?int;

    /**
     * Return the role name field
     *
     * @return string
     */
    public function getRole(): ?string;

    /**
     * Return the role label field
     *
     * @return string
     */
    public function getLabel(): ?string;

    /**
     * Set role name only for newly created role
     *
     * @param string|null $role Role name
     */
    public function setRole(?string $role): void;

    /**
     * Set the new label for role
     *
     * @param string|null $label New label
     */
    public function setLabel(?string $label): void;

    public function getType(): string;

    public function setType(string $type): void;
}
