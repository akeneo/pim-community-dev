<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

/**
 * The permission mask builder for 'Action' ACL extension
 */
class ActionMaskBuilder extends MaskBuilder
{
    const MASK_EXECUTE      = 1;         // 1 << 0

    const CODE_EXECUTE      = 'E';
}
