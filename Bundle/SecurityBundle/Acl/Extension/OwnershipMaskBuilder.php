<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

/**
 * The permission mask builder for 'Ownership' ACL extension
 */
class OwnershipMaskBuilder extends MaskBuilder
{
    // These access levels give a user access to own records and objects that are shared with the user.
    const MASK_VIEW_BASIC         = 1;          // 1 << 0
    const MASK_CREATE_BASIC       = 2;          // 1 << 1
    const MASK_EDIT_BASIC         = 4;          // 1 << 2
    const MASK_DELETE_BASIC       = 8;          // 1 << 3
    const MASK_ASSIGN_BASIC       = 16;         // 1 << 4
    const MASK_SHARE_BASIC        = 32;         // 1 << 5

    // These access levels give a user access to records in the user's business unit.
    const MASK_VIEW_LOCAL         = 64;         // 1 << 6
    const MASK_CREATE_LOCAL       = 128;        // 1 << 7
    const MASK_EDIT_LOCAL         = 256;        // 1 << 8
    const MASK_DELETE_LOCAL       = 512;        // 1 << 9
    const MASK_ASSIGN_LOCAL       = 1024;       // 1 << 10
    const MASK_SHARE_LOCAL        = 2048;       // 1 << 11

    // These access levels give a user access to records in the user's business unit
    // and all business units subordinate to the user's business unit.
    const MASK_VIEW_DEEP          = 4096;       // 1 << 12
    const MASK_CREATE_DEEP        = 8192;       // 1 << 13
    const MASK_EDIT_DEEP          = 16384;      // 1 << 14
    const MASK_DELETE_DEEP        = 32768;      // 1 << 15
    const MASK_ASSIGN_DEEP        = 65536;      // 1 << 16
    const MASK_SHARE_DEEP         = 131072;     // 1 << 17

    // These access levels give a user access to all records within the organization,
    // regardless of the business unit hierarchical level to which the instance or the user belongs.
    const MASK_VIEW_GLOBAL        = 262144;     // 1 << 18
    const MASK_CREATE_GLOBAL      = 524288;     // 1 << 19
    const MASK_EDIT_GLOBAL        = 1048576;    // 1 << 20
    const MASK_DELETE_GLOBAL      = 2097152;    // 1 << 21
    const MASK_ASSIGN_GLOBAL      = 4194304;    // 1 << 22
    const MASK_SHARE_GLOBAL       = 8388608;    // 1 << 23

    // These access levels give a user access to all records
    const MASK_VIEW_SYSTEM        = 16777216;   // 1 << 24
    const MASK_CREATE_SYSTEM      = 33554432;   // 1 << 25
    const MASK_EDIT_SYSTEM        = 67108864;   // 1 << 26
    const MASK_DELETE_SYSTEM      = 134217728;  // 1 << 27
    const MASK_ASSIGN_SYSTEM      = 268435456;  // 1 << 28
    const MASK_SHARE_SYSTEM       = 536870912;  // 1 << 29

    // Some useful groups of masks
    const GROUP_BASIC             = 47;         // 0x2F
    const GROUP_LOCAL             = 2256;       // 0x8D0
    const GROUP_DEEP              = 194304;     // 0x2F700
    const GROUP_GLOBAL            = 9240576;    // 0x8D0000
    const GROUP_SYSTEM            = 1056964608; // 0x3F000000
    const GROUP_CRUD_GLOBAL       = 3932160;    // 0x3C0000
    const GROUP_CRUD_SYSTEM       = 251658240;  // 0xF000000
    const GROUP_ALL               = 1073741823; // 0x3FFFFFFF

    const CODE_VIEW         = 'V';
    const CODE_CREATE       = 'C';
    const CODE_EDIT         = 'E';
    const CODE_DELETE       = 'D';
    const CODE_ASSIGN       = 'A';
    const CODE_SHARE        = 'S';

    const PATTERN_ALL_OFF   = '(SADECV) .. system:...... global:...... deep:...... local:...... basic:......';
}
