<?php

namespace Oro\Bundle\SecurityBundle\Acl\Permission;

use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;

/**
 * This is permission map complements the masks which have been defined
 * on the standard implementation of the MaskBuilder.
 */
class PermissionMap implements PermissionMapInterface
{
    const PERMISSION_VIEW           = 'VIEW';
    const PERMISSION_EDIT           = 'EDIT';
    const PERMISSION_CREATE         = 'CREATE';
    const PERMISSION_DELETE         = 'DELETE';
    const PERMISSION_ASSIGN         = 'ASSIGN';
    const PERMISSION_SHARE          = 'SHARE';
    const PERMISSION_OPERATOR       = 'OPERATOR';
    const PERMISSION_SHARE_OPERATOR = 'SHARE_OPERATOR';
    const PERMISSION_MASTER         = 'MASTER';
    const PERMISSION_EXECUTE        = 'EXECUTE';

    protected $map;

    /**
     * Constructor
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct()
    {
        $this->map = array(
            self::PERMISSION_VIEW => array(
                MaskBuilder::MASK_VIEW_BASIC,
                MaskBuilder::MASK_VIEW_LOCAL,
                MaskBuilder::MASK_VIEW_DEEP,
                MaskBuilder::MASK_VIEW_GLOBAL,
            ),

            self::PERMISSION_CREATE => array(
                MaskBuilder::MASK_CREATE_BASIC,
                MaskBuilder::MASK_CREATE_LOCAL,
                MaskBuilder::MASK_CREATE_DEEP,
                MaskBuilder::MASK_CREATE_GLOBAL,
            ),

            self::PERMISSION_EDIT => array(
                MaskBuilder::MASK_EDIT_BASIC,
                MaskBuilder::MASK_EDIT_LOCAL,
                MaskBuilder::MASK_EDIT_DEEP,
                MaskBuilder::MASK_EDIT_GLOBAL,
            ),

            self::PERMISSION_DELETE => array(
                MaskBuilder::MASK_DELETE_BASIC,
                MaskBuilder::MASK_DELETE_LOCAL,
                MaskBuilder::MASK_DELETE_DEEP,
                MaskBuilder::MASK_DELETE_GLOBAL,
            ),

            self::PERMISSION_ASSIGN => array(
                MaskBuilder::MASK_ASSIGN_BASIC,
                MaskBuilder::MASK_ASSIGN_LOCAL,
                MaskBuilder::MASK_ASSIGN_DEEP,
                MaskBuilder::MASK_ASSIGN_GLOBAL,
            ),

            self::PERMISSION_SHARE => array(
                MaskBuilder::MASK_SHARE_BASIC,
                MaskBuilder::MASK_SHARE_LOCAL,
                MaskBuilder::MASK_SHARE_DEEP,
                MaskBuilder::MASK_SHARE_GLOBAL,
            ),

            self::PERMISSION_OPERATOR => array(
                MaskBuilder::MASK_VIEW_BASIC,
                MaskBuilder::MASK_VIEW_LOCAL,
                MaskBuilder::MASK_VIEW_DEEP,
                MaskBuilder::MASK_VIEW_GLOBAL,
                MaskBuilder::MASK_CREATE_BASIC,
                MaskBuilder::MASK_CREATE_LOCAL,
                MaskBuilder::MASK_CREATE_DEEP,
                MaskBuilder::MASK_CREATE_GLOBAL,
                MaskBuilder::MASK_EDIT_BASIC,
                MaskBuilder::MASK_EDIT_LOCAL,
                MaskBuilder::MASK_EDIT_DEEP,
                MaskBuilder::MASK_EDIT_GLOBAL,
                MaskBuilder::MASK_DELETE_BASIC,
                MaskBuilder::MASK_DELETE_LOCAL,
                MaskBuilder::MASK_DELETE_DEEP,
                MaskBuilder::MASK_DELETE_GLOBAL,
            ),

            self::PERMISSION_SHARE_OPERATOR => array(
                MaskBuilder::MASK_VIEW_BASIC,
                MaskBuilder::MASK_VIEW_LOCAL,
                MaskBuilder::MASK_VIEW_DEEP,
                MaskBuilder::MASK_VIEW_GLOBAL,
                MaskBuilder::MASK_CREATE_BASIC,
                MaskBuilder::MASK_CREATE_LOCAL,
                MaskBuilder::MASK_CREATE_DEEP,
                MaskBuilder::MASK_CREATE_GLOBAL,
                MaskBuilder::MASK_EDIT_BASIC,
                MaskBuilder::MASK_EDIT_LOCAL,
                MaskBuilder::MASK_EDIT_DEEP,
                MaskBuilder::MASK_EDIT_GLOBAL,
                MaskBuilder::MASK_DELETE_BASIC,
                MaskBuilder::MASK_DELETE_LOCAL,
                MaskBuilder::MASK_DELETE_DEEP,
                MaskBuilder::MASK_DELETE_GLOBAL,
                MaskBuilder::MASK_SHARE_BASIC,
                MaskBuilder::MASK_SHARE_LOCAL,
                MaskBuilder::MASK_SHARE_DEEP,
                MaskBuilder::MASK_SHARE_GLOBAL,
            ),

            self::PERMISSION_MASTER => array(
                MaskBuilder::MASK_VIEW_BASIC,
                MaskBuilder::MASK_VIEW_LOCAL,
                MaskBuilder::MASK_VIEW_DEEP,
                MaskBuilder::MASK_VIEW_GLOBAL,
                MaskBuilder::MASK_CREATE_BASIC,
                MaskBuilder::MASK_CREATE_LOCAL,
                MaskBuilder::MASK_CREATE_DEEP,
                MaskBuilder::MASK_CREATE_GLOBAL,
                MaskBuilder::MASK_EDIT_BASIC,
                MaskBuilder::MASK_EDIT_LOCAL,
                MaskBuilder::MASK_EDIT_DEEP,
                MaskBuilder::MASK_EDIT_GLOBAL,
                MaskBuilder::MASK_DELETE_BASIC,
                MaskBuilder::MASK_DELETE_LOCAL,
                MaskBuilder::MASK_DELETE_DEEP,
                MaskBuilder::MASK_DELETE_GLOBAL,
                MaskBuilder::MASK_SHARE_BASIC,
                MaskBuilder::MASK_SHARE_LOCAL,
                MaskBuilder::MASK_SHARE_DEEP,
                MaskBuilder::MASK_SHARE_GLOBAL,
                MaskBuilder::MASK_ASSIGN_BASIC,
                MaskBuilder::MASK_ASSIGN_LOCAL,
                MaskBuilder::MASK_ASSIGN_DEEP,
                MaskBuilder::MASK_ASSIGN_GLOBAL,
            ),

            self::PERMISSION_EXECUTE => array(
                MaskBuilder::MASK_VIEW_BASIC,
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getMasks($permission, $object)
    {
        if (!isset($this->map[$permission])) {
            return null;
        }

        return $this->map[$permission];
    }

    /**
     * {@inheritDoc}
     */
    public function contains($permission)
    {
        return isset($this->map[$permission]);
    }
}
