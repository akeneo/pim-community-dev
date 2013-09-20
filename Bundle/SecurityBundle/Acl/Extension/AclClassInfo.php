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
}
