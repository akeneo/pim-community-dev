<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AceManipulationHelper;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\Batch\BatchItem;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AclManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $objectIdentityFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $aclProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $aceProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $extension;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $extensionSelector;

    protected function setUp(): void
    {
        $this->objectIdentityFactory =
            $this->getMockBuilder(ObjectIdentityFactory::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->extension = $this->createMock(AclExtensionInterface::class);
        $this->extension->expects($this->any())->method('getExtensionKey')->will($this->returnValue('entity'));
        $this->extension->expects($this->any())->method('getServiceBits')->will($this->returnValue(0));
        $this->extensionSelector = $this->getMockBuilder(AclExtensionSelector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionSelector->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->extension));

        $this->aclProvider = $this->getMockBuilder(MutableAclProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->aceProvider = $this->getMockBuilder(AceManipulationHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new AclManager(
            $this->objectIdentityFactory,
            $this->extensionSelector,
            $this->aclProvider,
            $this->aceProvider
        );
    }

    public function testIsAclEnabled()
    {
        $factory = $this->getMockBuilder(ObjectIdentityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionSelector = $this->getMockBuilder(AclExtensionSelector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager = new AclManager($factory, $extensionSelector);

        $this->assertFalse($manager->isAclEnabled());

        $aclProvider = $this->getMockBuilder(MutableAclProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $manager = new AclManager($factory, $extensionSelector, $aclProvider);

        $this->assertTrue($manager->isAclEnabled());
    }

    public function testGetOid()
    {
        $oid = new ObjectIdentity('test', 'test');
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($oid));

        $this->assertTrue($oid === $this->manager->getOid('test'));
    }

    public function testGetRootOid()
    {
        $oid = new ObjectIdentity('test', 'test');
        $this->objectIdentityFactory->expects($this->once())
            ->method('root')
            ->with($this->equalTo('test'))
            ->will($this->returnValue($oid));

        $this->assertTrue($oid === $this->manager->getRootOid('test'));
    }

    public function testDeleteAclShouldNotFailIfNoItems()
    {
        $oid = new ObjectIdentity('test', 'test');
        $this->manager->deleteAcl($oid);
    }

    public function testDeleteAclShouldMarkItemAsToDelete()
    {
        $oid = new ObjectIdentity('test', 'test');

        $this->setItem($oid, BatchItem::STATE_NONE);

        $this->manager->deleteAcl($oid);

        $items = $this->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals(BatchItem::STATE_DELETE, current($items)->getState());
    }

    public function testSetPermissionForNewAclIfGetAcesCalledBefore()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->throwException(new AclNotFoundException()));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->never())
            ->method('getAces');
        $this->aceProvider->expects($this->never())
            ->method('setPermission');

        $this->manager->getAces($sid, $oid);
        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testSetPermissionForRootOid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->once())
            ->method('setPermission')
            ->with(
                $this->identicalTo($acl),
                $this->identicalTo($this->extension),
                $this->equalTo(true),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            );

        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testSetPermissionForDomainObject()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->once())
            ->method('setPermission')
            ->with(
                $this->identicalTo($acl),
                $this->identicalTo($this->extension),
                $this->equalTo(true),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            );

        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testSetPermissionForEntityClass()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->once())
            ->method('setPermission')
            ->with(
                $this->identicalTo($acl),
                $this->identicalTo($this->extension),
                $this->equalTo(true),
                $this->equalTo(AclManager::CLASS_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            )
            ->will($this->returnValue(true));

        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFieldPermissionForRootOid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $this->manager->setFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testSetFieldPermissionForDomainObject()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->once())
            ->method('setPermission')
            ->with(
                $this->identicalTo($acl),
                $this->identicalTo($this->extension),
                $this->equalTo(true),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo($field),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            );

        $this->manager->setFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testSetFieldPermissionForEntityClass()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->once())
            ->method('setPermission')
            ->with(
                $this->identicalTo($acl),
                $this->identicalTo($this->extension),
                $this->equalTo(true),
                $this->equalTo(AclManager::CLASS_ACE),
                $this->equalTo($field),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            )
            ->will($this->returnValue(true));

        $this->manager->setFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeletePermissionForRootOid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deletePermission')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            );

        $this->manager->deletePermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testDeletePermissionForDomainObject()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deletePermission')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            );

        $this->manager->deletePermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testDeletePermissionForEntityClass()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deletePermission')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::CLASS_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            )
            ->will($this->returnValue(true));

        $this->manager->deletePermission($sid, $oid, $mask, $granting, $strategy);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteFieldPermissionForRootOid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $this->manager->deleteFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeleteFieldPermissionForDomainObject()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deletePermission')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo($field),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            );

        $this->manager->deleteFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeleteFieldPermissionForEntityClass()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deletePermission')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::CLASS_ACE),
                $this->equalTo($field),
                $this->identicalTo($sid),
                $this->equalTo($granting),
                $this->equalTo($mask),
                $this->equalTo($strategy)
            )
            ->will($this->returnValue(true));

        $this->manager->deleteFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeleteAllPermissionsForRootOid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deleteAllPermissions')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid)
            );

        $this->manager->deleteAllPermissions($sid, $oid);
    }

    public function testDeleteAllPermissionsForDomainObject()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deleteAllPermissions')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid)
            );

        $this->manager->deleteAllPermissions($sid, $oid);
    }

    public function testDeleteAllPermissionsForEntityClass()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deleteAllPermissions')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::CLASS_ACE),
                $this->equalTo(null),
                $this->identicalTo($sid)
            )
            ->will($this->returnValue(true));

        $this->manager->deleteAllPermissions($sid, $oid);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDeleteAllFieldPermissionsForRootOid()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $field = 'TestField';

        $this->manager->deleteAllFieldPermissions($sid, $oid, $field);
    }

    public function testDeleteAllFieldPermissionsForDomainObject()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $field = 'TestField';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deleteAllPermissions')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo($field),
                $this->identicalTo($sid)
            );

        $this->manager->deleteAllFieldPermissions($sid, $oid, $field);
    }

    public function testDeleteAllFieldPermissionsForEntityClass()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $field = 'TestField';

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->returnValue($acl));
        $this->aceProvider->expects($this->once())
            ->method('deleteAllPermissions')
            ->with(
                $this->identicalTo($acl),
                $this->equalTo(AclManager::CLASS_ACE),
                $this->equalTo($field),
                $this->identicalTo($sid)
            )
            ->will($this->returnValue(true));

        $this->manager->deleteAllFieldPermissions($sid, $oid, $field);
    }

    public function testSetPermissionForRootOidNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->throwException(new AclNotFoundException()));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->never())
            ->method('setPermission');

        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testSetPermissionForDomainObjectNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->throwException(new AclNotFoundException()));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->never())
            ->method('setPermission');

        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testSetPermissionForEntityClassNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->throwException(new AclNotFoundException()));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->never())
            ->method('setPermission');

        $this->manager->setPermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testSetFieldPermissionForDomainObjectNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->throwException(new AclNotFoundException()));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->never())
            ->method('setPermission');

        $this->manager->setFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testSetFieldPermissionForEntityClassNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $this->aclProvider->expects($this->once())
            ->method('findAcl')
            ->with($this->identicalTo($oid))
            ->will($this->throwException(new AclNotFoundException()));
        $this->extension->expects($this->once())
            ->method('validateMask')
            ->with($this->equalTo($mask), $this->identicalTo($oid));
        $this->aceProvider->expects($this->never())
            ->method('setPermission');

        $this->manager->setFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeletePermissionForRootOidNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deletePermission');

        $this->manager->deletePermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testDeletePermissionForDomainObjectNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deletePermission');

        $this->manager->deletePermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testDeletePermissionForEntityClassNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deletePermission');

        $this->manager->deletePermission($sid, $oid, $mask, $granting, $strategy);
    }

    public function testDeleteFieldPermissionForDomainObjectNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deletePermission');

        $this->manager->deleteFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeleteFieldPermissionForEntityClassNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $granting = true;
        $mask = 123;
        $strategy = 'any';
        $field = 'TestField';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deletePermission');

        $this->manager->deleteFieldPermission($sid, $oid, $field, $mask, $granting, $strategy);
    }

    public function testDeleteAllPermissionsForRootOidNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deleteAllPermissions');

        $this->manager->deleteAllPermissions($sid, $oid);
    }

    public function testDeleteAllPermissionsForDomainObjectNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deleteAllPermissions');

        $this->manager->deleteAllPermissions($sid, $oid);
    }

    public function testDeleteAllPermissionsForEntityClassNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deleteAllPermissions');

        $this->manager->deleteAllPermissions($sid, $oid);
    }

    public function testDeleteAllFieldPermissionsForDomainObjectNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity(123, 'Acme\Test');
        $field = 'TestField';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deleteAllPermissions');

        $this->manager->deleteAllFieldPermissions($sid, $oid, $field);
    }

    public function testDeleteAllFieldPermissionsForEntityClassNoAcl()
    {
        $sid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $oid = new ObjectIdentity('entity', 'Acme\Test');
        $field = 'TestField';

        $this->setItem($oid, BatchItem::STATE_CREATE);

        $this->aclProvider->expects($this->never())
            ->method('findAcl');
        $this->aceProvider->expects($this->never())
            ->method('deleteAllPermissions');

        $this->manager->deleteAllFieldPermissions($sid, $oid, $field);
    }

    public function testFlush()
    {
        $oid1 = new ObjectIdentity('Acme\Test1', 'entity');
        $oid2 = new ObjectIdentity('Acme\Test2', 'entity');
        $oid3 = new ObjectIdentity('Acme\Test3', 'entity');
        $oid4 = new ObjectIdentity('Acme\Test4', 'entity');

        $newItemSid = $this->createMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $newItem = new BatchItem($oid2, BatchItem::STATE_CREATE);
        $newItem->addAce(AclManager::OBJECT_ACE, 'TestField', $newItemSid, true, 123, 'all', true);

        $updateItemAcl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $deleteItemAcl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');

        $this->setItems(
            [
                new BatchItem($oid1, BatchItem::STATE_NONE),
                $newItem,
                new BatchItem($oid3, BatchItem::STATE_UPDATE, $updateItemAcl),
                new BatchItem($oid4, BatchItem::STATE_DELETE, $deleteItemAcl),
            ]
        );

        $this->aclProvider->expects($this->once())
            ->method('beginTransaction');
        $this->aclProvider->expects($this->once())
            ->method('commit');

        $acl = $this->createMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->aclProvider->expects($this->once())
            ->method('createAcl')
            ->with($this->identicalTo($oid2))
            ->will($this->returnValue($acl));

        $this->aceProvider->expects($this->once())
            ->method('setPermission')
            ->with(
                $this->identicalTo($acl),
                $this->identicalTo($this->extension),
                $this->equalTo(true),
                $this->equalTo(AclManager::OBJECT_ACE),
                $this->equalTo('TestField'),
                $this->identicalTo($newItemSid),
                $this->equalTo(true),
                $this->equalTo(123),
                $this->equalTo('all')
            )
            ->will($this->returnValue(true));

        $this->aclProvider->expects($this->exactly(2))
            ->method('updateAcl');

        $this->aclProvider->expects($this->once())
            ->method('deleteAcl')
            ->with($this->identicalTo($oid4));

        $this->manager->flush();
    }

    /**
     * @return BatchItem[]
     */
    private function getItems()
    {
        $class = new \ReflectionClass($this->manager);
        $prop = $class->getProperty('items');
        $prop->setAccessible(true);

        return $prop->getValue($this->manager);
    }

    private function setItem(ObjectIdentity $oid, $state, MutableAclInterface $acl = null)
    {
        $class = new \ReflectionClass($this->manager);
        $prop = $class->getProperty('items');
        $prop->setAccessible(true);

        $getKeyMtd = $class->getMethod('getKey');
        $getKeyMtd->setAccessible(true);
        $key = $getKeyMtd->invoke($this->manager, $oid);

        $prop->setValue($this->manager, [$key => new BatchItem($oid, $state, $acl)]);
    }

    /**
     * @param BatchItem[] $items
     */
    private function setItems($items)
    {
        $class = new \ReflectionClass($this->manager);
        $prop = $class->getProperty('items');
        $prop->setAccessible(true);

        $getKeyMtd = $class->getMethod('getKey');
        $getKeyMtd->setAccessible(true);

        $val = [];
        foreach ($items as $item) {
            $val[$getKeyMtd->invoke($this->manager, $item->getOid())] = $item;
        }

        $prop->setValue($this->manager, $val);
    }
}
