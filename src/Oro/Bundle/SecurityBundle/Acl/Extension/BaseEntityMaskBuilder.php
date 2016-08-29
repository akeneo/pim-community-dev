<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

/**
 * The base abstract permission mask builder for all permission mask builders of 'Entity' ACL extension
 *
 * This class allows to have up to 4 permission mask builders and as result
 * we can have 120 bits (4 * (32 - 2)) for bitmasks.
 * The last two bits are "service" bits and each permission mask builder must have unique combination of them.
 * Also please note that each bitmask must include the service bits of the permission mask builder where
 * the bitmask is declared.
 * Each mask must be declared as a constant. The constant name must be MASK_{permission}_{access level}.
 * Also the following constants must be declared in each permission mask builder:
 *  GROUP_BASIC, GROUP_LOCAL, GROUP_DEEP, GROUP_GLOBAL, GROUP_SYSTEM
 * and groups for all supported permissions in the following format: GROUP_{permission}
 * Each permission mask builder must have IDENTITY constant.
 */
abstract class BaseEntityMaskBuilder extends MaskBuilder
{
    const SERVICE_BITS = -1073741824;  // 0xC0000000
    const REMOVE_SERVICE_BITS = 1073741823;   // 0x3FFFFFFF

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->mask = $this->getConst('IDENTITY');

        return $this;
    }
}
