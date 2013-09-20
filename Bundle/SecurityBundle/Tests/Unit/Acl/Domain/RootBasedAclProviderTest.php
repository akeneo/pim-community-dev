<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Domain\RootBasedAclProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;

class RootBasedAclProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var RootBasedAclProvider */
    private $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $baseProvider;

    protected function setUp()
    {
        $this->baseProvider = $this->getMock('Symfony\Component\Security\Acl\Model\AclProviderInterface');
        $this->provider = new RootBasedAclProvider(
            new ObjectIdentityFactory(
                TestHelper::get($this)->createAclExtensionSelector()
            )
        );
        $this->provider->setBaseAclProvider($this->baseProvider);
    }

    public function testFindChildren()
    {
        $oid = new ObjectIdentity('test', 'Test');
        $this->baseProvider->expects($this->once())
            ->method('findChildren')
            ->with($this->identicalTo($oid), $this->equalTo(true))
            ->will($this->returnValue(array()));

        $this->assertEquals(array(), $this->provider->findChildren($oid, true));
    }

    public function testFindAcls()
    {
        $oids = array(new ObjectIdentity('test', 'Test'));
        $sids = array($this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface'));
        $this->baseProvider->expects($this->once())
            ->method('findAcls')
            ->with($this->equalTo($oids), $this->equalTo($sids))
            ->will($this->returnValue(array()));

        $this->assertEquals(array(), $this->provider->findAcls($oids, $sids));
    }

    public function testFindAcl()
    {
        $oid = new ObjectIdentity('test', 'Test');
        $rootOid = new ObjectIdentity('test', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $sids = array($this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface'));
        $acl = $this->getMockBuilder('Symfony\Component\Security\Acl\Domain\Acl')
            ->disableOriginalConstructor()
            ->getMock();
        $rootAcl = $this->getMockBuilder('Symfony\Component\Security\Acl\Domain\Acl')
            ->disableOriginalConstructor()
            ->getMock();
        $this->baseProvider->expects($this->at(0))
            ->method('findAcl')
            ->with($this->identicalTo($oid), $this->equalTo($sids))
            ->will($this->returnValue($acl));
        $this->baseProvider->expects($this->at(1))
            ->method('findAcl')
            ->with($this->equalTo($rootOid), $this->equalTo($sids))
            ->will($this->returnValue($rootAcl));

        $result = $this->provider->findAcl($oid, $sids);
        $this->assertInstanceOf('Oro\Bundle\SecurityBundle\Acl\Domain\RootBasedAclWrapper', $result);
    }

    public function testFindAclShouldReturnRootAcl()
    {
        $oid = new ObjectIdentity('test', 'Test');
        $sids = array($this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface'));
        $rootOid = new ObjectIdentity('test', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $rootAcl = $this->getMock('Symfony\Component\Security\Acl\Model\AclInterface');
        $this->baseProvider->expects($this->at(0))
            ->method('findAcl')
            ->with($this->identicalTo($oid), $this->equalTo($sids))
            ->will($this->throwException(new AclNotFoundException()));
        $this->baseProvider->expects($this->at(1))
            ->method('findAcl')
            ->with($this->equalTo($rootOid), $this->equalTo($sids))
            ->will($this->returnValue($rootAcl));

        $this->assertTrue($rootAcl === $this->provider->findAcl($oid, $sids));
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\AclNotFoundException
     */
    public function testFindAclShouldThrowAclNotFoundException()
    {
        $oid = new ObjectIdentity('test', 'Test');
        $sids = array($this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface'));
        $rootOid = new ObjectIdentity('test', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
        $this->baseProvider->expects($this->at(0))
            ->method('findAcl')
            ->with($this->identicalTo($oid), $this->equalTo($sids))
            ->will($this->throwException(new AclNotFoundException()));
        $this->baseProvider->expects($this->at(1))
            ->method('findAcl')
            ->with($this->equalTo($rootOid), $this->equalTo($sids))
            ->will($this->throwException(new AclNotFoundException()));

        $this->provider->findAcl($oid, $sids);
    }
}
