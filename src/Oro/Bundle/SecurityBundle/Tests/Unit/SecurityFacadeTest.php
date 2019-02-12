<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit;

use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class SecurityFacadeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SecurityFacade
     */
    private $facade;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $tokenStorage;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $annotationProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $objectIdentityFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock('Symfony\Component\Security\Core\TokenStorageInterface');
        $this->annotationProvider =
            $this->getMockBuilder(AclAnnotationProvider::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->objectIdentityFactory =
            $this->getMockBuilder(ObjectIdentityFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->logger = $this->createMock('Psr\Log\LoggerInterface');

        $this->facade = new SecurityFacade(
            $this->tokenStorage,
            $this->annotationProvider,
            $this->objectIdentityFactory,
            $this->logger
        );
    }

    public function testIsClassMethodGrantedDenyingByMethodAcl()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $annotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('method_annotation'));
        $annotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION'));

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with('TestClass', 'TestMethod')
            ->will($this->returnValue($annotation));
        $this->logger->expects($this->once())
            ->method('debug');
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($oid));
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION'), $this->identicalTo($oid))
            ->will($this->returnValue(false));

        $result = $this->facade->isClassMethodGranted('TestClass', 'TestMethod');
        $this->assertFalse($result);
    }

    public function testIsClassMethodGrantedGrantingByMethodAclNoClassAcl()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $annotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('method_annotation'));
        $annotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION'));
        $annotation->expects($this->once())
            ->method('getIgnoreClassAcl')
            ->will($this->returnValue(false));

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with('TestClass', 'TestMethod')
            ->will($this->returnValue($annotation));
        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with('TestClass')
            ->will($this->returnValue(null));
        $this->logger->expects($this->once())
            ->method('debug');
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($oid));
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION'), $this->identicalTo($oid))
            ->will($this->returnValue(true));

        $result = $this->facade->isClassMethodGranted('TestClass', 'TestMethod');
        $this->assertTrue($result);
    }

    public function testIsClassMethodGrantedGrantingByMethodAclWithIgnoreClassAcl()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $annotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('method_annotation'));
        $annotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION'));
        $annotation->expects($this->once())
            ->method('getIgnoreClassAcl')
            ->will($this->returnValue(true));

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with('TestClass', 'TestMethod')
            ->will($this->returnValue($annotation));
        $this->logger->expects($this->once())
            ->method('debug');
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($oid));
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION'), $this->identicalTo($oid))
            ->will($this->returnValue(true));

        $result = $this->facade->isClassMethodGranted('TestClass', 'TestMethod');
        $this->assertTrue($result);
    }

    public function testIsClassMethodGrantedDenyingByClassAcl()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $annotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('method_annotation'));
        $annotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION'));
        $annotation->expects($this->once())
            ->method('getIgnoreClassAcl')
            ->will($this->returnValue(false));

        $classOid = new ObjectIdentity('2', 'TestType');
        $classAnnotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classAnnotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('class_annotation'));
        $classAnnotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION_CLASS'));

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with('TestClass', 'TestMethod')
            ->will($this->returnValue($annotation));
        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with('TestClass')
            ->will($this->returnValue($classAnnotation));
        $this->logger->expects($this->exactly(2))
            ->method('debug');
        $this->objectIdentityFactory->expects($this->at(0))
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($oid));
        $this->objectIdentityFactory->expects($this->at(1))
            ->method('get')
            ->with($this->identicalTo($classAnnotation))
            ->will($this->returnValue($classOid));
        $this->tokenStorage->expects($this->at(0))
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION'), $this->identicalTo($oid))
            ->will($this->returnValue(true));
        $this->tokenStorage->expects($this->at(1))
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION_CLASS'), $this->identicalTo($classOid))
            ->will($this->returnValue(false));

        $result = $this->facade->isClassMethodGranted('TestClass', 'TestMethod');
        $this->assertFalse($result);
    }

    public function testIsClassMethodGrantedGrantingByMethodAndClassAcls()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $annotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('method_annotation'));
        $annotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION'));
        $annotation->expects($this->once())
            ->method('getIgnoreClassAcl')
            ->will($this->returnValue(false));

        $classOid = new ObjectIdentity('2', 'TestType');
        $classAnnotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classAnnotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('class_annotation'));
        $classAnnotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION_CLASS'));

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with('TestClass', 'TestMethod')
            ->will($this->returnValue($annotation));
        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with('TestClass')
            ->will($this->returnValue($classAnnotation));
        $this->logger->expects($this->exactly(2))
            ->method('debug');
        $this->objectIdentityFactory->expects($this->at(0))
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($oid));
        $this->objectIdentityFactory->expects($this->at(1))
            ->method('get')
            ->with($this->identicalTo($classAnnotation))
            ->will($this->returnValue($classOid));
        $this->tokenStorage->expects($this->at(0))
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION'), $this->identicalTo($oid))
            ->will($this->returnValue(true));
        $this->tokenStorage->expects($this->at(1))
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION_CLASS'), $this->identicalTo($classOid))
            ->will($this->returnValue(true));

        $result = $this->facade->isClassMethodGranted('TestClass', 'TestMethod');
        $this->assertTrue($result);
    }

    public function testIsGrantedWithAclAnnotationId()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $annotation = $this->getMockBuilder(Acl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $annotation->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('method_annotation'));
        $annotation->expects($this->once())
            ->method('getPermission')
            ->will($this->returnValue('TEST_PERMISSION'));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($oid));
        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotationById')
            ->with('TestAnnotation')
            ->will($this->returnValue($annotation));
        $this->logger->expects($this->once())
            ->method('debug');
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST_PERMISSION'), $this->identicalTo($oid))
            ->will($this->returnValue(true));

        $result = $this->facade->isGranted('TestAnnotation');
        $this->assertTrue($result);
    }

    public function testIsGrantedWithRoleName()
    {
        $this->annotationProvider->expects($this->once())
            ->method('findAnnotationById')
            ->with('TestRole')
            ->will($this->returnValue(null));
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TestRole'), $this->equalTo(null))
            ->will($this->returnValue(true));

        $result = $this->facade->isGranted('TestRole');
        $this->assertTrue($result);
    }

    public function testIsGrantedWithRoleNames()
    {
        $this->annotationProvider->expects($this->never())
            ->method('findAnnotationById');
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(['TestRole1', 'TestRole2']), $this->equalTo(null))
            ->will($this->returnValue(true));

        $result = $this->facade->isGranted(['TestRole1', 'TestRole2']);
        $this->assertTrue($result);
    }

    public function testIsGrantedWithString()
    {
        $oid = new ObjectIdentity('1', 'TestType');
        $obj = 'Entity:SomeClass';
        $this->annotationProvider->expects($this->never())
            ->method('findAnnotationById');
        $this->objectIdentityFactory->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo($obj))
            ->will($this->returnValue($oid));
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('PERMISSION'), $oid)
            ->will($this->returnValue(true));

        $result = $this->facade->isGranted('PERMISSION', $obj);
        $this->assertTrue($result);
    }

    public function testIsGrantedWithObject()
    {
        $obj = new \stdClass();
        $this->annotationProvider->expects($this->never())
            ->method('findAnnotationById');
        $this->tokenStorage->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('PERMISSION'), $this->equalTo($obj))
            ->will($this->returnValue(true));

        $result = $this->facade->isGranted('PERMISSION', $obj);
        $this->assertTrue($result);
    }
}
