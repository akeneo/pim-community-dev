<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\EventListener;

use Oro\Bundle\SecurityBundle\EventListener\ControllerListener;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject;
use Oro\Bundle\SecurityBundle\Annotation\Acl as AclAnnotation;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class ControllerListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $className = 'Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject';
    protected $methodName = 'getId';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $annotationProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $objectIdentityFactory;

    /** @var Request */
    protected $request;

    /** @var ControllerListener */
    protected $listener;

    /** @var FilterControllerEvent */
    protected $event;

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
        $this->listener = new ControllerListener(
            new SecurityFacade(
                $this->securityContext,
                $this->annotationProvider,
                $this->objectIdentityFactory,
                $logger
            ),
            $logger
        );
    }

    public function testInterceptWithNoAnnotation()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
            )
            ->will($this->returnValue(null));
        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->className
            )
            ->will($this->returnValue(null));

        $this->securityContext->expects($this->never())
            ->method('isGranted');

        $this->listener->onKernelController($event);
    }

    public function testInterceptAccessGranted()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $classAnnotation = new AclAnnotation(array('id' => 'test_class', 'type' => 'test', 'permission' => 'TEST'));
        $classIdentity = new ObjectIdentity('123', 'test_class');
        $methodAnnotation = new AclAnnotation(array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST'));
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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
                $this->className
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

        $this->listener->onKernelController($event);
    }

    public function testInterceptAccessGrantedWithIgnoreClassAcl()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $methodAnnotation = new AclAnnotation(
            array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST', 'ignore_class_acl' => true)
        );
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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

        $this->listener->onKernelController($event);
    }

    public function testInterceptAccessGrantedWithoutClassAcl()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $methodAnnotation = new AclAnnotation(
            array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST')
        );
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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
                $this->className
            )
            ->will($this->returnValue(null));

        $this->listener->onKernelController($event);
    }

    public function testInterceptAccessGrantedByClassAcl()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $classAnnotation = new AclAnnotation(array('id' => 'test_class', 'type' => 'test', 'permission' => 'TEST'));
        $classIdentity = new ObjectIdentity('123', 'test_class');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
            )
            ->will($this->returnValue(null));

        $this->annotationProvider->expects($this->at(1))
            ->method('findAnnotation')
            ->with(
                $this->className
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

        $this->listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testInterceptAccessDeniedByClassAcl()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $classAnnotation = new AclAnnotation(array('id' => 'test_class', 'type' => 'test', 'permission' => 'TEST'));
        $classIdentity = new ObjectIdentity('123', 'test_class');
        $methodAnnotation = new AclAnnotation(array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST'));
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->at(0))
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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
                $this->className
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

        $this->listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testInterceptAccessDeniedByMethodAcl()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $methodAnnotation = new AclAnnotation(
            array('id' => 'test_method', 'type' => 'test', 'permission' => 'TEST', 'ignore_class_acl' => true)
        );
        $methodIdentity = new ObjectIdentity('123', 'test_method');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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

        $this->listener->onKernelController($event);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function testInterceptAccessDenied()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $annotation = new AclAnnotation(array('id' => 'test', 'type' => 'test', 'permission' => 'TEST'));
        $identity = new ObjectIdentity('123', 'test');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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

        $this->listener->onKernelController($event);
    }

    public function testInterceptAccessDeniedForInternalAction()
    {
        $event = new FilterControllerEvent(
            $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface'),
            array(new TestDomainObject(), $this->methodName),
            $this->request,
            HttpKernelInterface::SUB_REQUEST
        );

        $this->request->attributes->remove('_route');

        $annotation = new AclAnnotation(array('id' => 'test', 'type' => 'test', 'permission' => 'TEST'));
        $identity = new ObjectIdentity('123', 'test');

        $this->annotationProvider->expects($this->once())
            ->method('findAnnotation')
            ->with(
                $this->className,
                $this->methodName
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

        $this->listener->onKernelController($event);
    }
}
