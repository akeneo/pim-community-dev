<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

/**
 * The permission mask builder for 'Aspect' ACL extension
 */
class AspectMaskBuilder extends MaskBuilder
{
    const MASK_VIEW         = 1;         // 1 << 0
    const MASK_CREATE       = 2;         // 1 << 1
    const MASK_EDIT         = 4;         // 1 << 2
    const MASK_DELETE       = 8;         // 1 << 3

    const CODE_VIEW         = 'V';
    const CODE_CREATE       = 'C';
    const CODE_EDIT         = 'E';
    const CODE_DELETE       = 'D';
}
