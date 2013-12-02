<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Acl\Domain\RootBasedAclProvider;
use Oro\Bundle\SecurityBundle\Tests\Unit\TestHelper;
use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;

class RootBasedAclProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var RootBasedAclProvider */
    private $provider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $baseProvider;

    private $acl;
    private $rootAcl;
    private $underlyingAcl;

    private $oid;
    private $rootOid;
    private $underlyingOid;

    protected function setUp()
    {


        $this->baseProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Dbal\MutableAclProvider')
            ->disableOriginalConstructor()
            ->getMock();
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
        $rootOid = new ObjectIdentity('entity', ObjectIdentityFactory::ROOT_IDENTITY_TYPE);
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

    /**
     * @@dataProvider aclTestData
     */
    public function testFindAclData($oids, $findAcl, $expect, $parameter)
    {
        list($oid, $rootOid, $underlyingOid) = $oids;
        $factory = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $sids = array($this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface'));
        $this->baseProvider->expects($this->any())
            ->method('cacheEmptyAcl');

        $this->baseProvider->expects($this->any())
            ->method('findAcl')
            ->will(
                $this->returnCallback(
                    function ($oid, $sids) use ($findAcl) {
                        if (isset($findAcl[$this->getOidKey($oid)])) {

                            return $findAcl[$this->getOidKey($oid)];
                        }
                        throw new AclNotFoundException('Acl not found');
                    }
                )
            );

        $factory->expects($this->any())
            ->method('root')
            ->with($this->equalTo($oid))
            ->will($this->returnValue($rootOid));

        $factory->expects($this->any())
            ->method('underlying')
            ->with($this->equalTo($oid))
            ->will($this->returnValue($underlyingOid));

        $provider = new RootBasedAclProvider($factory);
        $provider->setBaseAclProvider($this->baseProvider);

        if (empty($findAcl)) {
            $this->setExpectedException('Symfony\Component\Security\Acl\Exception\AclNotFoundException');
        }

        $resultAcl = $provider->findAcl($oid, $sids);
        $this->$expect($resultAcl, $parameter);
    }

    public function aclTestData()
    {
        $strategy = $this->getMockForAbstractClass(
            'Symfony\Component\Security\Acl\Model\PermissionGrantingStrategyInterface'
        );
        $this->oid = new ObjectIdentity('test', 'Test');
        $this->rootOid = new ObjectIdentity('entity', '(root)');
        $this->underlyingOid = new ObjectIdentity('entity', 'Test');

        $this->acl = new Acl(1, $this->oid, $strategy, [], false);
        $this->rootAcl = new Acl(1, $this->rootOid, $strategy, [], false);
        $this->underlyingAcl = new Acl(1, $this->underlyingOid, $strategy, [], false);

        return [
            [
                [$this->oid, $this->rootOid, $this->underlyingOid],
                [
                    $this->getOidKey($this->oid) => $this->acl,
                    $this->getOidKey($this->rootOid) => $this->rootAcl
                ],
                'results1',
                $this->acl
            ],
            [
                [$this->oid, $this->rootOid, $this->underlyingOid],
                [$this->getOidKey($this->oid) => $this->acl],
                'results2',
                $this->acl
            ],
            [
                [$this->oid, $this->rootOid, $this->underlyingOid],
                [
                    $this->getOidKey($this->rootOid) => $this->rootAcl,
                    $this->getOidKey($this->underlyingOid) => $this->underlyingAcl
                ],
                'results3',
                $this->underlyingAcl
            ]
        ];
    }

    protected function results1($result, $parameter)
    {
        $this->assertInstanceOf(
            'Oro\Bundle\SecurityBundle\Acl\Domain\RootBasedAclWrapper',
            $result
        );
        $classReflection = new \ReflectionClass($result);
        $aclReflection = $classReflection->getProperty('acl');
        $aclReflection->setAccessible(true);
        $this->assertEquals($parameter, $aclReflection->getValue($result));
    }

    protected function results2($result, $parameter)
    {
        $this->assertEquals($parameter, $result);
    }

    protected function results3($result, $parameter)
    {
        $reflection = new \ReflectionClass($result);
        $aclReflection = $reflection->getProperty('acl');
        $aclReflection->setAccessible(true);
        $this->assertEquals($parameter, $aclReflection->getValue($result));
    }

    protected function getOidKey(ObjectIdentity $oid)
    {
        return $oid->getIdentifier() . $oid->getType();
    }
}
