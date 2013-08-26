<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Permission;

use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionMap;
use Oro\Bundle\SecurityBundle\Acl\Extension\EntityMaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionMaskBuilder;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\Entity\TestEntity;

class PermissionMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionMap
     */
    private $map;

    protected function setUp()
    {
        $this->map = new PermissionMap(
            TestHelper::get($this)->createAclExtensionSelector()
        );
    }

    public function testGetMasksReturnsNullWhenNotSupportedMask()
    {
        $this->assertNull($this->map->getMasks('IS_AUTHENTICATED_REMEMBERED', null));
    }

    /**
     * @dataProvider getMasksProvider
     */
    public function testGetMasks($object, $name, $mask)
    {
        $this->assertEquals($mask, $this->map->getMasks($name, $object));
    }

    /**
     * @dataProvider containsProvider
     */
    public function testContains($name, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->map->contains($name));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getMasksProvider()
    {
        return array(
            array(new TestEntity(), 'VIEW', array(
                EntityMaskBuilder::MASK_VIEW_BASIC,
                EntityMaskBuilder::MASK_VIEW_LOCAL,
                EntityMaskBuilder::MASK_VIEW_DEEP,
                EntityMaskBuilder::MASK_VIEW_GLOBAL,
            )),
            array(new TestEntity(), 'CREATE', array(
                EntityMaskBuilder::MASK_CREATE_BASIC,
                EntityMaskBuilder::MASK_CREATE_LOCAL,
                EntityMaskBuilder::MASK_CREATE_DEEP,
                EntityMaskBuilder::MASK_CREATE_GLOBAL,
            )),
            array(new TestEntity(), 'EDIT', array(
                EntityMaskBuilder::MASK_EDIT_BASIC,
                EntityMaskBuilder::MASK_EDIT_LOCAL,
                EntityMaskBuilder::MASK_EDIT_DEEP,
                EntityMaskBuilder::MASK_EDIT_GLOBAL,
            )),
            array(new TestEntity(), 'DELETE', array(
                EntityMaskBuilder::MASK_DELETE_BASIC,
                EntityMaskBuilder::MASK_DELETE_LOCAL,
                EntityMaskBuilder::MASK_DELETE_DEEP,
                EntityMaskBuilder::MASK_DELETE_GLOBAL,
            )),
            array(new TestEntity(), 'ASSIGN', array(
                EntityMaskBuilder::MASK_ASSIGN_BASIC,
                EntityMaskBuilder::MASK_ASSIGN_LOCAL,
                EntityMaskBuilder::MASK_ASSIGN_DEEP,
                EntityMaskBuilder::MASK_ASSIGN_GLOBAL,
            )),
            array(new TestEntity(), 'SHARE', array(
                EntityMaskBuilder::MASK_SHARE_BASIC,
                EntityMaskBuilder::MASK_SHARE_LOCAL,
                EntityMaskBuilder::MASK_SHARE_DEEP,
                EntityMaskBuilder::MASK_SHARE_GLOBAL,
            )),
            array('action: test', 'EXECUTE', array(
                ActionMaskBuilder::MASK_EXECUTE,
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
            array('EXECUTE', true),
            array('OTHER', false),
        );
    }
}
