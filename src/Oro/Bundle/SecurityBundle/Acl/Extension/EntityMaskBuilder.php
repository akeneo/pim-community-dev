<?php

namespace Oro\Bundle\SecurityBundle\Acl\Extension;

/**
 * The permission mask builder for 'Entity' ACL extension.
 *
 * This class provides masks for the following permissions:
 *  VIEW, CREATE, EDIT, DELETE, ASSIGN and SHARE
 */
final class EntityMaskBuilder extends BaseEntityMaskBuilder
{
    public const IDENTITY = 0;

    // These access levels give a user access to own records and objects that are shared with the user.
    public const MASK_VIEW_BASIC = 1;          // 1 << 0     + IDENTITY
    public const MASK_CREATE_BASIC = 2;          // 1 << 1     + IDENTITY
    public const MASK_EDIT_BASIC = 4;          // 1 << 2     + IDENTITY
    public const MASK_DELETE_BASIC = 8;          // 1 << 3     + IDENTITY
    public const MASK_ASSIGN_BASIC = 16;         // 1 << 4     + IDENTITY
    public const MASK_SHARE_BASIC = 32;         // 1 << 5     + IDENTITY

    // These access levels give a user access to records in all business units are assigned to the user.
    public const MASK_VIEW_LOCAL = 64;         // 1 << 6     + IDENTITY
    public const MASK_CREATE_LOCAL = 128;        // 1 << 7     + IDENTITY
    public const MASK_EDIT_LOCAL = 256;        // 1 << 8     + IDENTITY
    public const MASK_DELETE_LOCAL = 512;        // 1 << 9     + IDENTITY
    public const MASK_ASSIGN_LOCAL = 1024;       // 1 << 10    + IDENTITY
    public const MASK_SHARE_LOCAL = 2048;       // 1 << 11    + IDENTITY

    // These access levels give a user access to records in all business units are assigned to the user
    // and all business units subordinate to business units are assigned to the user.
    public const MASK_VIEW_DEEP = 4096;       // 1 << 12    + IDENTITY
    public const MASK_CREATE_DEEP = 8192;       // 1 << 13    + IDENTITY
    public const MASK_EDIT_DEEP = 16384;      // 1 << 14    + IDENTITY
    public const MASK_DELETE_DEEP = 32768;      // 1 << 15    + IDENTITY
    public const MASK_ASSIGN_DEEP = 65536;      // 1 << 16    + IDENTITY
    public const MASK_SHARE_DEEP = 131072;     // 1 << 17    + IDENTITY

    // These access levels give a user access to all records within the organization,
    // regardless of the business unit hierarchical level to which the domain object belongs
    // or the user is assigned to.
    public const MASK_VIEW_GLOBAL = 262144;     // 1 << 18    + IDENTITY
    public const MASK_CREATE_GLOBAL = 524288;     // 1 << 19    + IDENTITY
    public const MASK_EDIT_GLOBAL = 1048576;    // 1 << 20    + IDENTITY
    public const MASK_DELETE_GLOBAL = 2097152;    // 1 << 21    + IDENTITY
    public const MASK_ASSIGN_GLOBAL = 4194304;    // 1 << 22    + IDENTITY
    public const MASK_SHARE_GLOBAL = 8388608;    // 1 << 23    + IDENTITY

    // These access levels give a user access to all records within the system.
    public const MASK_VIEW_SYSTEM = 16777216;   // 1 << 24    + IDENTITY
    public const MASK_CREATE_SYSTEM = 33554432;   // 1 << 25    + IDENTITY
    public const MASK_EDIT_SYSTEM = 67108864;   // 1 << 26    + IDENTITY
    public const MASK_DELETE_SYSTEM = 134217728;  // 1 << 27    + IDENTITY
    public const MASK_ASSIGN_SYSTEM = 268435456;  // 1 << 28    + IDENTITY
    public const MASK_SHARE_SYSTEM = 536870912;  // 1 << 29    + IDENTITY

    // Some useful groups of bitmasks
    public const GROUP_BASIC = 63;         // 0x3F       + IDENTITY
    public const GROUP_LOCAL = 4032;       // 0xFC0      + IDENTITY
    public const GROUP_DEEP = 258048;     // 0x3F000    + IDENTITY
    public const GROUP_GLOBAL = 16515072;   // 0xFC0000   + IDENTITY
    public const GROUP_SYSTEM = 1056964608; // 0x3F000000 + IDENTITY
    public const GROUP_VIEW = 17043521;   // 0x1041041  + IDENTITY
    public const GROUP_CREATE = 34087042;   // 0x2082082  + IDENTITY
    public const GROUP_EDIT = 68174084;   // 0x4104104  + IDENTITY
    public const GROUP_DELETE = 136348168;  // 0x8208208  + IDENTITY
    public const GROUP_ASSIGN = 272696336;  // 0x10410410 + IDENTITY
    public const GROUP_SHARE = 545392672;  // 0x20820820 + IDENTITY
    public const GROUP_CRUD_SYSTEM = 251658240;  // 0xF000000  + IDENTITY
    public const GROUP_NONE = self::IDENTITY;
    public const GROUP_ALL = 1073741823; // 0x3FFFFFFF + IDENTITY

    public const CODE_VIEW = 'V';
    public const CODE_CREATE = 'C';
    public const CODE_EDIT = 'E';
    public const CODE_DELETE = 'D';
    public const CODE_ASSIGN = 'A';
    public const CODE_SHARE = 'S';

    public const PATTERN_ALL_OFF = '(SADECV) .. system:...... global:...... deep:...... local:...... basic:......';
}
