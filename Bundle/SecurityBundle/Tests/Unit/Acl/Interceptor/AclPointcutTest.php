<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Interceptor;

use Oro\Bundle\SecurityBundle\Acl\Interceptor\AclPointcut;

class AclPointcutTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $annotationProvider;

    /** @var AclPointcut */
    protected $pointcut;

    protected function setUp()
    {
        $this->annotationProvider = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pointcut = new AclPointcut($this->annotationProvider);
    }

    public function testMatchesClass()
    {
        $reflection = new \ReflectionClass('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject');

        $this->annotationProvider->expects($this->once())
            ->method('isProtectedClass')
            ->with($this->equalTo('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject'))
            ->will($this->returnValue(true));

        $this->assertTrue($this->pointcut->matchesClass($reflection));
    }

    public function testMatchesMethod()
    {
        $reflection = new \ReflectionClass('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject');
        $reflectionMethod = $reflection->getMethod('getId');

        $this->annotationProvider->expects($this->at(0))
            ->method('isProtectedMethod')
            ->with(
                $this->equalTo('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject'),
                $this->equalTo('getId')
            )
            ->will($this->returnValue(false));
        $this->annotationProvider->expects($this->at(1))
            ->method('hasAnnotation')
            ->with($this->equalTo('Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Domain\Fixtures\TestDomainObject'))
            ->will($this->returnValue(true));

        $this->assertTrue($this->pointcut->matchesMethod($reflectionMethod));
    }
}
