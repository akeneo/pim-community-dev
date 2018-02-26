<?php

namespace Pim\Component\User\Model;

use Symfony\Component\Security\Core\Role\RoleInterface as SymfonyRoleInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @todo This "write" model should not extends Symfony\Component\Security\Core\Role\RoleInterface.
 * We should create a "read" model that implement that class.
 */
interface RoleInterface extends SymfonyRoleInterface
{
    /**
     * Return the role id
     *
     * @return int
     */
    public function getId();

    /**
     * Return the role name field
     *
     * @return string
     */
    public function getRole();

    /**
     * Return the role label field
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set role name only for newly created role
     *
     * @param  string            $role Role name
     *
     * @throws \RuntimeException
     */
    public function setRole($role);

    /**
     * Set the new label for role
     *
     * @param  string $label New label
     */
    public function setLabel($label);
}