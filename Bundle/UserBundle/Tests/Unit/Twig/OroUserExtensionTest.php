<?php

namespace Oro\Bundle\UserBundle\Tests\Twig;

use Oro\Bundle\UserBundle\Twig\OroUserExtension;

class OroUserExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $twigExtension;

    private $manager;

    public function setUp()
    {
        $this->manager = $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Acl\ManagerInterface');
        $this->twigExtension = new OroUserExtension($this->manager);
    }

    public function testGetName()
    {
        $this->assertEquals('user_extension', $this->twigExtension->getName());
    }

    public function testGetFunctions()
    {
        $filters = $this->twigExtension->getFunctions();
        $this->assertEquals(1, count($filters));
    }

    public function testCheckResourceIsGranted()
    {
        $this->manager->expects($this->once())
            ->method('isResourceGranted')
            ->with($this->equalTo('test_acl'))
            ->will($this->returnValue(true));

        $this->assertTrue($this->twigExtension->checkResourceIsGranted('test_acl'));
    }
}
