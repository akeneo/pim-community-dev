<?php

namespace Oro\Bundle\SecurityBundle\Tests\Twig;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Twig\OroSecurityExtension;

class OroSecurityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroSecurityExtension
     */
    protected $twigExtension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityFacade;

    protected function setUp(): void
    {
        $this->securityFacade = $this->getMockBuilder(SecurityFacade::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigExtension = new OroSecurityExtension($this->securityFacade);
    }

    protected function tearDown()
    {
        unset($this->securityFacade);
        unset($this->twigExtension);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_security_extension', $this->twigExtension->getName());
    }

    public function testGetFunctions()
    {
        $expectedFunctions = [
            'resource_granted' => 'checkResourceIsGranted',
        ];

        $actualFunctions = $this->twigExtension->getFunctions();
        $this->assertSameSize($expectedFunctions, $actualFunctions);

        foreach ($expectedFunctions as $twigFunction => $internalMethod) {
            $this->assertArrayHasKey($twigFunction, $actualFunctions);
            $this->assertInstanceOf('\Twig_SimpleFunction', $actualFunctions[$twigFunction]);
            $this->assertAttributeEquals($internalMethod, 'method', $actualFunctions[$twigFunction]);
        }
    }

    public function testCheckResourceIsGranted()
    {
        $this->securityFacade->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('test_acl'))
            ->will($this->returnValue(true));

        $this->assertTrue($this->twigExtension->checkResourceIsGranted('test_acl'));
    }
}
