<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Permission;

use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionMap;
use Oro\Bundle\SecurityBundle\Acl\Permission\MaskBuilder;

class PermissionMapTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMasksReturnsNullWhenNotSupportedMask()
    {
        $map = new PermissionMap();
        $this->assertNull($map->getMasks('IS_AUTHENTICATED_REMEMBERED', null));
    }

    /**
     * @dataProvider getMasksProvider
     */
    public function testGetMasks($name, $mask)
    {
        $map = new PermissionMap();
        $this->assertEquals($mask, $map->getMasks($name, null));
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains($name, $expectedResult)
    {
        $map = new PermissionMap();
        $this->assertEquals($expectedResult, $map->contains($name));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getMasksProvider()
    {
        return array(
            array('VIEW', array(
                MaskBuilder::MASK_VIEW_BASIC,
                MaskBuilder::MASK_VIEW_LOCAL,
                MaskBuilder::MASK_VIEW_DEEP,
                MaskBuilder::MASK_VIEW_GLOBAL,
            )),
            array('CREATE', array(
                MaskBuilder::MASK_CREATE_BASIC,
                MaskBuilder::MASK_CREATE_LOCAL,
                MaskBuilder::MASK_CREATE_DEEP,
                MaskBuilder::MASK_CREATE_GLOBAL,
            )),
            array('EDIT', array(
                MaskBuilder::MASK_EDIT_BASIC,
                MaskBuilder::MASK_EDIT_LOCAL,
                MaskBuilder::MASK_EDIT_DEEP,
                MaskBuilder::MASK_EDIT_GLOBAL,
            )),
            array('DELETE', array(
                MaskBuilder::MASK_DELETE_BASIC,
                MaskBuilder::MASK_DELETE_LOCAL,
                MaskBuilder::MASK_DELETE_DEEP,
                MaskBuilder::MASK_DELETE_GLOBAL,
            )),
            array('ASSIGN', array(
                MaskBuilder::MASK_ASSIGN_BASIC,
                MaskBuilder::MASK_ASSIGN_LOCAL,
                MaskBuilder::MASK_ASSIGN_DEEP,
                MaskBuilder::MASK_ASSIGN_GLOBAL,
            )),
            array('SHARE', array(
                MaskBuilder::MASK_SHARE_BASIC,
                MaskBuilder::MASK_SHARE_LOCAL,
                MaskBuilder::MASK_SHARE_DEEP,
                MaskBuilder::MASK_SHARE_GLOBAL,
            )),
            array('OPERATOR', array(
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
            )),
            array('SHARE_OPERATOR', array(
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
            )),
            array('MASTER', array(
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
            )),
            array('EXECUTE', array(
                MaskBuilder::MASK_VIEW_BASIC,
            )),
        );
    }

    public static function containsProvider()
    {
        return array(
            array('VIEW', true),
            array('EDIT', true),
            array('CREATE', true),
            array('DELETE', true),
            array('ASSIGN', true),
            array('SHARE', true),
            array('OPERATOR', true),
            array('SHARE_OPERATOR', true),
            array('MASTER', true),
            array('EXECUTE', true),
            array('OTHER', false),
        );
    }
}
