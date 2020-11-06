<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

interface AclClassInfo
{
    /**
     * Gets the class name
     */
    public function getClassName(): string;

    /**
     * Gets the security group name
     */
    public function getGroup(): string;

    /**
     * Gets a label
     */
    public function getLabel(): string;

    public function isEnabledAtCreation(): bool;
}
