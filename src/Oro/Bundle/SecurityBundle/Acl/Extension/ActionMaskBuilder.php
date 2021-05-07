<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

/**
 * The permission mask builder for 'Action' ACL extension
 */
final class ActionMaskBuilder extends MaskBuilder
{
    public const MASK_EXECUTE = 1;         // 1 << 0

    // Some useful groups of bitmasks
    public const GROUP_NONE = 0;
    public const GROUP_ALL = 1;         // 1 << 0

    public const CODE_EXECUTE = 'E';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
}
