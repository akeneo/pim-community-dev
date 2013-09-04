<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Persistence;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\EntryInterface;

class AclPrivilegeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var AclPrivilegeRepository */
    private $repository;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $extensionSelector;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $extension;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $aceProvider;

    protected function setUp()
    {
        $this->extension = $this->getMock('Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface');

        $this->extensionSelector = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionSelector')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionSelector->expects($this->any())
            ->method('select')
            ->will($this->returnValue($this->extension));

        $this->aceProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Persistence\AceManipulationHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager->expects($this->any())
            ->method('getExtensionSelector')
            ->will($this->returnValue($this->extensionSelector));
        $this->manager->expects($this->any())
            ->method('getAllExtensions')
            ->will($this->returnValue(array($this->extension)));
        $this->manager->expects($this->any())
            ->method('getAceProvider')
            ->will($this->returnValue($this->aceProvider));

        $this->repository = new AclPrivilegeRepository($this->manager);
    }

    public function testGetPermissionNames()
    {
        $rootId = 'test';
        $permissions = array('VIEW', 'EDIT');

        $this->manager->expects($this->once())
            ->method('getRootOid')
            ->with($this->equalTo($rootId))
            ->will($this->returnValue(new ObjectIdentity($rootId, ObjectIdentityFactory::ROOT_IDENTITY_TYPE)));
        $this->extension->expects($this->once())
            ->method('getPermissions')
            ->will($this->returnValue($permissions));

        $this->assertEquals(
            $permissions,
            $this->repository->getPermissionNames($rootId)
        );
    }

    public function testGetPermissionNamesForSeveralAclExtensions()
    {
        $rootId1 = 'test1';
        $permissions1 = array('VIEW', 'EDIT');

        $rootId2 = 'test2';
        $permissions2 = array('VIEW', 'CREATE');

        $this->manager->expects($this->exactly(2))
            ->method('getRootOid')
            ->will(
                $this->returnValueMap(
                    array(
                        array($rootId1, new ObjectIdentity($rootId1, ObjectIdentityFactory::ROOT_IDENTITY_TYPE)),
                        array($rootId2, new ObjectIdentity($rootId2, ObjectIdentityFactory::ROOT_IDENTITY_TYPE)),
                    )
                )
            );
        $this->extension->expects($this->at(0))
            ->method('getPermissions')
            ->will($this->returnValue($permissions1));
        $this->extension->expects($this->at(1))
            ->method('getPermissions')
            ->will($this->returnValue($permissions2));

        $this->assertEquals(
            array('VIEW', 'EDIT', 'CREATE'),
            $this->repository->getPermissionNames(array($rootId1, $rootId2))
        );
    }

    public function testGetPrivileges()
    {
        $rootId = 'test';
        $classes = array(
            'Acme\Class1',
            'Acme\Class2',
        );

        $rootOid = new ObjectIdentity($rootId, ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $rootAcl = $this->getMock('Symfony\Component\Security\Acl\Model\AclInterface');

        $oid1 = new ObjectIdentity($rootId, $classes[0]);
        $oid1Acl = $this->getMock('Symfony\Component\Security\Acl\Model\AclInterface');
        $oid2 = new ObjectIdentity($rootId, $classes[1]);

        $oids = array($oid1, $oid2);
        $oidsWithRoot = array($rootOid, $oid1, $oid2);

        $aclsSrc = array(
            array('oid' => $rootOid, 'acl' => $rootAcl),
            array('oid' => $oid1, 'acl' => $oid1Acl),
            array('oid' => $oid2, 'acl' => null),
        );

        $allowedPermissions = array();
        $allowedPermissions[(string)$rootOid] = array('VIEW', 'CREATE', 'EDIT');
        $allowedPermissions[(string)$oid1] = array('VIEW', 'CREATE', 'EDIT');
        $allowedPermissions[(string)$oid2] = array('VIEW', 'CREATE');

        $rootAce = $this->getMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $rootAce->expects($this->any())->method('isGranting')->will($this->returnValue(true));
        $rootAce->expects($this->any())->method('getMask')->will($this->returnValue('root'));
        $rootAcl->expects($this->any())
            ->method('getObjectAces')
            ->will($this->returnValue(array($rootAce)));
        $rootAcl->expects($this->never())
            ->method('getClassAces');

        $oid1Ace = $this->getMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $oid1Ace->expects($this->any())->method('isGranting')->will($this->returnValue(true));
        $oid1Ace->expects($this->any())->method('getMask')->will($this->returnValue('oid1'));
        $oid1Acl->expects($this->any())
            ->method('getClassAces')
            ->will($this->returnValue(array($oid1Ace)));
        $oid1Acl->expects($this->once())
            ->method('getObjectAces')
            ->will($this->returnValue(array()));

        $sid = $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

        $this->extension->expects($this->once())
            ->method('getRootId')
            ->will($this->returnValue($rootId));
        $this->extension->expects($this->once())
            ->method('getClasses')
            ->will($this->returnValue($classes));
        $this->extension->expects($this->any())
            ->method('getAllowedPermissions')
            ->will(
                $this->returnCallback(
                    function ($oid) use (&$allowedPermissions) {
                        return $allowedPermissions[(string)$oid];
                    }
                )
            );
        $this->extension->expects($this->any())
            ->method('getPermissions')
            ->will($this->returnValue(array('VIEW', 'CREATE', 'EDIT')));
        $this->extension->expects($this->any())
            ->method('getAccessLevel')
            ->will(
                $this->returnCallback(
                    function ($mask, $permission) {
                        switch ($permission) {
                            case 'VIEW':
                                if ($mask === 'root') {
                                    return AccessLevel::GLOBAL_LEVEL;
                                } elseif ($mask === 'oid1') {
                                    return AccessLevel::BASIC_LEVEL;
                                }
                                break;
                            case 'CREATE':
                                if ($mask === 'root') {
                                    return AccessLevel::DEEP_LEVEL;
                                } elseif ($mask === 'oid1') {
                                    return AccessLevel::BASIC_LEVEL;
                                }
                                break;
                            case 'EDIT':
                                if ($mask === 'root') {
                                    return AccessLevel::LOCAL_LEVEL;
                                } elseif ($mask === 'oid1') {
                                    return AccessLevel::NONE_LEVEL;
                                }
                                break;
                        }
                        return AccessLevel::NONE_LEVEL;
                    }
                )
            );

        $this->manager->expects($this->once())
            ->method('getRootOid')
            ->with($this->equalTo($rootId))
            ->will($this->returnValue($rootOid));

        $this->manager->expects($this->once())
            ->method('findAcls')
            ->with($this->identicalTo($sid), $this->equalTo($oidsWithRoot))
            ->will(
                $this->returnCallback(
                    function () use (&$aclsSrc) {
                        return AclPrivilegeRepositoryTest::getAcls($aclsSrc);
                    }
                )
            );

        $this->aceProvider->expects($this->any())
            ->method('getAces')
            ->will(
                $this->returnCallback(
                    function ($acl, $type, $field) use (&$rootAcl, &$oid1Acl) {
                        if ($acl === $oid1Acl) {
                            $a = $oid1Acl;
                        } else {
                            $a = $rootAcl;
                        }

                        return $a->{"get{$type}Aces"}();
                    }
                )
            );

        $result = $this->repository->getPrivileges($sid);

        $this->assertCount(count($classes) + 1, $result);
        $this->assertEquals('test:' . ObjectIdentityFactory::ROOT_IDENTITY_TYPE, $result[0]->getIdentity()->getId());
        $this->assertEquals('test:Acme\Class2', $result[1]->getIdentity()->getId());
        $this->assertEquals('test:Acme\Class1', $result[2]->getIdentity()->getId());

        $this->assertEquals(3, $result[0]->getPermissionCount());
        $this->assertEquals(2, $result[1]->getPermissionCount());
        $this->assertEquals(3, $result[2]->getPermissionCount());

        $p = $result[0]->getPermissions();
        $this->assertEquals(AccessLevel::GLOBAL_LEVEL, $p['VIEW']->getAccessLevel());
        $this->assertEquals(AccessLevel::DEEP_LEVEL, $p['CREATE']->getAccessLevel());
        $this->assertEquals(AccessLevel::LOCAL_LEVEL, $p['EDIT']->getAccessLevel());

        $p = $result[1]->getPermissions();
        $this->assertEquals(AccessLevel::GLOBAL_LEVEL, $p['VIEW']->getAccessLevel());
        $this->assertEquals(AccessLevel::DEEP_LEVEL, $p['CREATE']->getAccessLevel());
        $this->assertFalse($p->containsKey('EDIT'));

        $p = $result[2]->getPermissions();
        $this->assertEquals(AccessLevel::BASIC_LEVEL, $p['VIEW']->getAccessLevel());
        $this->assertEquals(AccessLevel::BASIC_LEVEL, $p['CREATE']->getAccessLevel());
        $this->assertEquals(AccessLevel::NONE_LEVEL, $p['EDIT']->getAccessLevel());
    }

    /**
     * @param array $src
     * @return \SplObjectStorage
     * @throws NotAllAclsFoundException
     */
    private static function getAcls(array $src)
    {
        $isPartial = false;
        $acls = new \SplObjectStorage();
        foreach ($src as $item) {
            if ($item['acl'] !== null) {
                $acls->attach($item['oid'], $item['acl']);
            } else {
                $isPartial = true;
            }
        }

        if ($isPartial) {
            $ex = new NotAllAclsFoundException();
            $ex->setPartialResult($acls);
            throw $ex;
        }

        return $acls;
    }
}
