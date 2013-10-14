<?php

namespace Oro\Bundle\HelpBundle\Tests\Unit\Twig;

use Oro\Bundle\HelpBundle\Twig\HelpExtension;

class HelpExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkProvider;

    /**
     * @var HelpExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->linkProvider = $this->getMockBuilder('Oro\Bundle\HelpBundle\Model\HelpLinkProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new HelpExtension($this->linkProvider);
    }

    public function testGetFunctions()
    {
        /** @var \Twig_SimpleFunction $helpLinkFunction */
        list($helpLinkFunction) = $this->extension->getFunctions();

        $this->assertInstanceOf('Twig_SimpleFunction', $helpLinkFunction);
        $this->assertEquals('get_help_link', $helpLinkFunction->getName());
        $this->assertEquals(array($this->extension, 'getHelpLinkUrl'), $helpLinkFunction->getCallable());
    }

    public function testGetHelpLinkUrl()
    {
        $expects = 'http://server.com/help/list';

        $this->linkProvider
            ->expects($this->once())
            ->method('getHelpLinkUrl')
            ->will($this->returnValue($expects));

        $this->assertEquals($expects, $this->extension->getHelpLinkUrl());
    }

    public function testGetName()
    {
        $this->assertEquals(HelpExtension::NAME, $this->extension->getName());
    }
}
