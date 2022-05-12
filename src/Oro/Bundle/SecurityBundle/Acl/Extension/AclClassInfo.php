<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

interface AclClassInfo
{
    /**
     * Gets the class name
     *
     * @return string
     */
    public function getClassName();

    /**
     * Gets the security group name
     *
     * @return string
     */
    public function getGroup();

    /**
     * Gets a label
     *
     * @return string
     */
    public function getLabel();

    public function isEnabledAtCreation(): bool;

    public function getOrder(): int;

    /**
     * Returns true if the ACL must be visible in the UI. eg: the edit role permissions screen
     * ACL that are not visible still exist and can be managed by the code.
     */
    public function isVisible(): bool;
}
