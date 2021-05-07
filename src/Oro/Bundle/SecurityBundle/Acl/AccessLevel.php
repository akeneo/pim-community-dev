<?php

namespace Oro\Bundle\SecurityBundle\Acl;

/**
 * This class defines all available access levels in the BAP security system.
 */
final class AccessLevel
{
    /**
     * Names of all access levels
     *
     * @var string[]
     */
    public static $allAccessLevelNames = ['BASIC', 'LOCAL', 'DEEP', 'GLOBAL', 'SYSTEM'];

    public const NONE_LEVEL_NAME = 'NONE';

    /**
     * Unknown access level.
     */
    public const UNKNOWN = -1;

    /**
     * Deny access.
     */
    public const NONE_LEVEL = 0;

    /**
     * This access level gives a user access to own records and objects that are shared with the user.
     */
    public const BASIC_LEVEL = 1;

    /**
     * This access level gives a user access to records in all business units are assigned to the user.
     */
    public const LOCAL_LEVEL = 2;

    /**
     * This access level gives a user access to records in all business units are assigned to the user
     * and all business units subordinate to business units are assigned to the user.
     */
    public const DEEP_LEVEL = 3;

    /**
     * This access level gives a user access to all records within the organization,
     * regardless of the business unit hierarchical level to which the domain object belongs
     * or the user is assigned to.
     */
    public const GLOBAL_LEVEL = 4;

    /**
     * This access level gives a user access to all records within the system.
     */
    public const SYSTEM_LEVEL = 5;

    /**
     * Gets constant value by its name
     *
     * @param string $name
     * @return int
     */
    public static function getConst($name)
    {
        return constant('self::' . $name);
    }

    /**
     * Gets the name of an access level by the given value of the constant
     *
     * @param int $value
     * @return string|null
     */
    public static function getAccessLevelName($value)
    {
        if ($value > self::NONE_LEVEL) {
            return self::$allAccessLevelNames[$value - 1];
        }

        return null;
    }
}
