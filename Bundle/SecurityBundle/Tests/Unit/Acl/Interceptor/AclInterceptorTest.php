<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Interceptor;

use Oro\Bundle\SecurityBundle\Acl\Interceptor\AclInterceptor;
use CG\Proxy\MethodInvocation;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

class AclInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $annotationProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $objectIdentityFactory;

    /** @var Request */
    protected $request;

    /** @var MethodInvocation */
    protected $methodInvocation;

    /** @var AclInterceptor */
    protected $interceptor;

    protected function setUp()
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->securityContext = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->annotationProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectIdentityFactory =
            $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $this->request = new Request();
        $this->request->attributes->add(array('_route' => 'test'));
        $this->interceptor = new AclInterceptor(
            new SecurityFacade(
                $this->securityContext,
                $this->annotationProvider,
                $this->objectIdentityFactory,
                $logger
            ),
            $this->request,
            $logger
        );

        $reflection = new \ReflectionClass('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject');
        $reflectionMethod = $reflection->getMethod('getId');
        $this->methodInvocation = new MethodInvocation(
            $reflectionMethod,
            new TestDomainObject(),
            array(),
            array()
        );
    }

    public function testInterceptWithNoAnnotation()
    {
        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue(null));
        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class
            )
            ->will($this->returnValue(null));

        $this->securityContext->expects($this->never())
            ->method('isGranted');

        $result = $this->interceptor->intercept($this->methodInvocation);
        $this->assertEquals('getId()', $result);
    }

    public function testInterceptAccessGranted()
    {
        $classAnnotation = new AclAnnotation(array('id' => 'test_class', 'type' => 'test', 'permission' => 'TEST'));
        $classIdentity = new ObjectIdentity('123', 'test_class');
        $methodAnnotation = new AclAnnotation(array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST'));
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($methodAnnotation));
        $this->objectIdentityFactory->expects($this->at(0))
            ->method('get')
            ->with($this->identicalTo($methodAnnotation))
            ->will($this->returnValue($methodIdentity));
        $this->securityContext->expects($this->at(0))
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($methodIdentity))
            ->will($this->returnValue(true));

        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class
            )
            ->will($this->returnValue($classAnnotation));
        $this->objectIdentityFactory->expects($this->at(1))
            ->method('get')
            ->with($this->identicalTo($classAnnotation))
            ->will($this->returnValue($classIdentity));
        $this->securityContext->expects($this->at(1))
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($classIdentity))
            ->will($this->returnValue(true));

        $result = $this->interceptor->intercept($this->methodInvocation);
        $this->assertEquals('getId()', $result);
    }

    public function testInterceptAccessGrantedWithIgnoreClassAcl()
    {
        $methodAnnotation = new AclAnnotation(
            array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST', 'ignore_class_acl' => true)
        );
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($methodAnnotation));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($methodAnnotation))
            ->will($this->returnValue($methodIdentity));
        $this->securityContext->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($methodIdentity))
            ->will($this->returnValue(true));

        $result = $this->interceptor->intercept($this->methodInvocation);
        $this->assertEquals('getId()', $result);
    }

    public function testInterceptAccessGrantedWithoutClassAcl()
    {
        $methodAnnotation = new AclAnnotation(
            array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST')
        );
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($methodAnnotation));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($methodAnnotation))
            ->will($this->returnValue($methodIdentity));
        $this->securityContext->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($methodIdentity))
            ->will($this->returnValue(true));

        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class
            )
            ->will($this->returnValue(null));

        $result = $this->interceptor->intercept($this->methodInvocation);
        $this->assertEquals('getId()', $result);
    }

    public function testInterceptAccessGrantedByClassAcl()
    {
        $classAnnotation = new AclAnnotation(array('id' => 'test_class', 'type' => 'test', 'permission' => 'TEST'));
        $classIdentity = new ObjectIdentity('123', 'test_class');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue(null));

        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class
            )
            ->will($this->returnValue($classAnnotation));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($classAnnotation))
            ->will($this->returnValue($classIdentity));
        $this->securityContext->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($classIdentity))
            ->will($this->returnValue(true));

        $result = $this->interceptor->intercept($this->methodInvocation);
        $this->assertEquals('getId()', $result);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testInterceptAccessDeniedByClassAcl()
    {
        $classAnnotation = new AclAnnotation(array('id' => 'test_class', 'type' => 'test', 'permission' => 'TEST'));
        $classIdentity = new ObjectIdentity('123', 'test_class');
        $methodAnnotation = new AclAnnotation(array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST'));
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($methodAnnotation));
        $this->objectIdentityFactory->expects($this->at(0))
            ->method('get')
            ->with($this->identicalTo($methodAnnotation))
            ->will($this->returnValue($methodIdentity));
        $this->securityContext->expects($this->at(0))
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($methodIdentity))
            ->will($this->returnValue(true));

        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class
            )
            ->will($this->returnValue($classAnnotation));
        $this->objectIdentityFactory->expects($this->at(1))
            ->method('get')
            ->with($this->identicalTo($classAnnotation))
            ->will($this->returnValue($classIdentity));
        $this->securityContext->expects($this->at(1))
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($classIdentity))
            ->will($this->returnValue(false));

        $this->interceptor->intercept($this->methodInvocation);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testInterceptAccessDeniedByMethodAcl()
    {
        $methodAnnotation = new AclAnnotation(
            array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST', 'ignore_class_acl' => true)
        );
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($methodAnnotation));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($methodAnnotation))
            ->will($this->returnValue($methodIdentity));
        $this->securityContext->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($methodIdentity))
            ->will($this->returnValue(false));

        $this->interceptor->intercept($this->methodInvocation);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testInterceptAccessDenied()
    {
        $annotation = new AclAnnotation(array('id' => 'test', 'type' => 'test', 'permission' => 'TEST'));
        $identity = new ObjectIdentity('123', 'test');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($annotation));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($identity));

        $this->securityContext->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($identity))
            ->will($this->returnValue(false));

        $this->interceptor->intercept($this->methodInvocation);
    }

    public function testInterceptAccessDeniedForInternalAction()
    {
        $this->request->attributes->remove('_route');

        $annotation = new AclAnnotation(array('id' => 'test', 'type' => 'test', 'permission' => 'TEST'));
        $identity = new ObjectIdentity('123', 'test');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->methodInvocation->reflection->class,
                $this->methodInvocation->reflection->name
            )
            ->will($this->returnValue($annotation));
        $this->objectIdentityFactory->expects($this->once())
            ->method('get')
            ->with($this->identicalTo($annotation))
            ->will($this->returnValue($identity));

        $this->securityContext->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('TEST'), $this->identicalTo($identity))
            ->will($this->returnValue(false));

        $result = $this->interceptor->intercept($this->methodInvocation);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
    }
}
