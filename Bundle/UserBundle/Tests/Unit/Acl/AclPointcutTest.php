<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Acl;

use Oro\Bundle\UserBundle\Acl\AclPointcut;

use Oro\Bundle\UserBundle\Tests\Unit\Fixture\Controller\MainTestController;
use Oro\Bundle\UserBundle\Tests\Unit\Fixture\Controller\BadClass;

class AclPointcutTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchesClass()
    {
        $pointcut = new AclPointcut();

        $this->assertEquals(true, $pointcut->matchesClass(new \ReflectionClass(new MainTestController())));
        $this->assertEquals(false, $pointcut->matchesClass(new \ReflectionClass(new BadClass())));
    }

    public function testMatchesMethod()
    {
        $pointcut = new AclPointcut();

        $testControllerReflection = new \ReflectionClass(new MainTestController());

        $this->assertEquals(true, $pointcut->matchesMethod($testControllerReflection->getMethod('test1Action')));
        $this->assertEquals(true, $pointcut->matchesMethod($testControllerReflection->getMethod('testNoAclAction')));
        $this->assertEquals(false, $pointcut->matchesMethod($testControllerReflection->getMethod('noActionMethod')));
    }
}
