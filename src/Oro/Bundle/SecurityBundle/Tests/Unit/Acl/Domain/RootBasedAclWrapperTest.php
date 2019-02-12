<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\RootBasedAclWrapper;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RootBasedAclWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $acl;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $rootAcl;

    protected function setUp(): void
    {
        $this->acl = $this->getMockBuilder('Symfony\Component\Security\Acl\Domain\Acl')
            ->disableOriginalConstructor()
            ->getMock();
        $this->rootAcl = $this->getMockBuilder('Symfony\Component\Security\Acl\Domain\Acl')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetClassAces()
    {
        $sid1 = new RoleSecurityIdentity('sid1');
        $sid2 = new RoleSecurityIdentity('sid2');

        $ace = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $rootAce1 = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $rootAce2 = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');

        $ace->expects($this->any())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid1));
        $rootAce1->expects($this->any())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid1));
        $rootAce2->expects($this->any())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid2));

        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('getClassAces')
            ->will($this->returnValue([$ace]));
        $this->rootAcl->expects($this->once())
            ->method('getObjectAces')
            ->will($this->returnValue([$rootAce1, $rootAce2]));

        $result = $obj->getClassAces();

        $this->assertEquals([$ace, $rootAce2], $result);
    }

    public function testGetClassFieldAces()
    {
        $sid1 = new RoleSecurityIdentity('sid1');
        $sid2 = new RoleSecurityIdentity('sid2');

        $ace = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $rootAce1 = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $rootAce2 = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');

        $ace->expects($this->any())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid1));
        $rootAce1->expects($this->any())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid1));
        $rootAce2->expects($this->any())
            ->method('getSecurityIdentity')
            ->will($this->returnValue($sid2));

        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('getClassFieldAces')
            ->with($this->equalTo('SomeField'))
            ->will($this->returnValue([$ace]));
        $this->rootAcl->expects($this->once())
            ->method('getObjectFieldAces')
            ->with($this->equalTo('SomeField'))
            ->will($this->returnValue([$rootAce1, $rootAce2]));

        $result = $obj->getClassFieldAces('SomeField');

        $this->assertEquals([$ace, $rootAce2], $result);
    }

    public function testGetObjectAces()
    {
        $ace = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');

        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('getObjectAces')
            ->will($this->returnValue([$ace]));
        $result = $obj->getObjectAces();

        $this->assertEquals([$ace], $result);
    }

    public function testGetObjectFieldAces()
    {
        $ace = $this->createMock('Symfony\Component\Security\Acl\Model\EntryInterface');

        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('getObjectFieldAces')
            ->with($this->equalTo('SomeField'))
            ->will($this->returnValue([$ace]));
        $result = $obj->getObjectFieldAces('SomeField');

        $this->assertEquals([$ace], $result);
    }

    public function testGetObjectIdentity()
    {
        $id = new ObjectIdentity('1', 'SomeType');
        $this->acl->expects($this->once())
            ->method('getObjectAces')
            ->will($this->returnValue(['test']));
        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('getObjectIdentity')
            ->will($this->returnValue($id));
        $result = $obj->getObjectIdentity();

        $this->assertTrue($id === $result);
    }

    public function testGetParentAcl()
    {
        $parentAcl = $this->getMockBuilder('Symfony\Component\Security\Acl\Domain\Acl')
            ->disableOriginalConstructor()
            ->getMock();

        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('getParentAcl')
            ->will($this->returnValue($parentAcl));
        $result = $obj->getParentAcl();

        $this->assertTrue($parentAcl === $result);
    }

    public function testIsEntriesInheriting()
    {
        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('isEntriesInheriting')
            ->will($this->returnValue(true));
        $result = $obj->isEntriesInheriting();

        $this->assertTrue($result);
    }

    public function testIsSidLoaded()
    {
        $sid = new RoleSecurityIdentity('sid1');

        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $this->acl->expects($this->once())
            ->method('isSidLoaded')
            ->with($this->identicalTo($sid))
            ->will($this->returnValue(true));
        $result = $obj->isSidLoaded($sid);

        $this->assertTrue($result);
    }

    public function testIsGranted()
    {
        $sid = new RoleSecurityIdentity('sid1');

        $strategy = $this->createMock('Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface');

        $obj = $this->getMockBuilder(RootBasedAclWrapper::class)
            ->setConstructorArgs([$this->acl, $this->rootAcl])
            ->setMethods(['getPermissionGrantingStrategy'])
            ->getMock();
        $obj->expects($this->once())
            ->method('getPermissionGrantingStrategy')
            ->will($this->returnValue($strategy));
        $strategy->expects($this->once())
            ->method('isGranted')
            ->with(
                $this->identicalTo($obj),
                $this->equalTo([1]),
                $this->equalTo([$sid]),
                $this->equalTo(true)
            )
            ->will($this->returnValue(true));

        $result = $obj->isGranted([1], [$sid], true);

        $this->assertTrue($result);
    }

    public function testIsFieldGranted()
    {
        $sid = new RoleSecurityIdentity('sid1');

        $strategy = $this->createMock('Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface');

        $obj = $this->getMockBuilder(RootBasedAclWrapper::class)
            ->setConstructorArgs([$this->acl, $this->rootAcl])
            ->setMethods(['getPermissionGrantingStrategy'])
            ->getMock();
        $obj->expects($this->once())
            ->method('getPermissionGrantingStrategy')
            ->will($this->returnValue($strategy));
        $strategy->expects($this->once())
            ->method('isFieldGranted')
            ->with(
                $this->identicalTo($obj),
                $this->equalTo('SomeField'),
                $this->equalTo([1]),
                $this->equalTo([$sid]),
                $this->equalTo(true)
            )
            ->will($this->returnValue(true));

        $result = $obj->isFieldGranted('SomeField', [1], [$sid], true);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \LogicException
     */
    public function testSerialize()
    {
        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $obj->serialize();
    }

    /**
     * @expectedException \LogicException
     */
    public function testUnserialize()
    {
        $obj = new RootBasedAclWrapper($this->acl, $this->rootAcl);
        $obj->unserialize('');
    }
}
