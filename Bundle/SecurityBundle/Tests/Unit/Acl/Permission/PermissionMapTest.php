<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Permission;

use Oro\Bundle\SecurityBundle\Acl\Permission\PermissionMap;
use Oro\Bundle\SecurityBundle\Acl\Extension\OwnershipMaskBuilder;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionMaskBuilder;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;

class PermissionMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionMap
     */
    private $map;

    protected function setUp()
    {
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->map = new PermissionMap(
            TestHelper::createAclExtensionSelector($em)
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
            array(new \stdClass(), 'VIEW', array(
                OwnershipMaskBuilder::MASK_VIEW_BASIC,
                OwnershipMaskBuilder::MASK_VIEW_LOCAL,
                OwnershipMaskBuilder::MASK_VIEW_DEEP,
                OwnershipMaskBuilder::MASK_VIEW_GLOBAL,
            )),
            array(new \stdClass(), 'CREATE', array(
                OwnershipMaskBuilder::MASK_CREATE_BASIC,
                OwnershipMaskBuilder::MASK_CREATE_LOCAL,
                OwnershipMaskBuilder::MASK_CREATE_DEEP,
                OwnershipMaskBuilder::MASK_CREATE_GLOBAL,
            )),
            array(new \stdClass(), 'EDIT', array(
                OwnershipMaskBuilder::MASK_EDIT_BASIC,
                OwnershipMaskBuilder::MASK_EDIT_LOCAL,
                OwnershipMaskBuilder::MASK_EDIT_DEEP,
                OwnershipMaskBuilder::MASK_EDIT_GLOBAL,
            )),
            array(new \stdClass(), 'DELETE', array(
                OwnershipMaskBuilder::MASK_DELETE_BASIC,
                OwnershipMaskBuilder::MASK_DELETE_LOCAL,
                OwnershipMaskBuilder::MASK_DELETE_DEEP,
                OwnershipMaskBuilder::MASK_DELETE_GLOBAL,
            )),
            array(new \stdClass(), 'ASSIGN', array(
                OwnershipMaskBuilder::MASK_ASSIGN_BASIC,
                OwnershipMaskBuilder::MASK_ASSIGN_LOCAL,
                OwnershipMaskBuilder::MASK_ASSIGN_DEEP,
                OwnershipMaskBuilder::MASK_ASSIGN_GLOBAL,
            )),
            array(new \stdClass(), 'SHARE', array(
                OwnershipMaskBuilder::MASK_SHARE_BASIC,
                OwnershipMaskBuilder::MASK_SHARE_LOCAL,
                OwnershipMaskBuilder::MASK_SHARE_DEEP,
                OwnershipMaskBuilder::MASK_SHARE_GLOBAL,
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
