<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Twig;

use Oro\Bundle\NavigationBundle\Twig\TitleExtension;

class TitleExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $service;

    /**
     * @var TitleExtension
     */
    private $extension;

    public function setUp()
    {
        $this->service = $this->getMock('Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface');
        $this->extension = new TitleExtension($this->service);
    }

    public function testFunctionDeclaration()
    {
        $functions = $this->extension->getFunctions();
        $this->assertArrayHasKey('oro_title_render', $functions);
        $this->assertArrayHasKey('oro_title_render_short', $functions);
        $this->assertArrayHasKey('oro_title_render_serialized', $functions);
    }

    public function testNameConfigured()
    {
        $this->assertInternalType('string', $this->extension->getName());
    }

    public function testRenderSerialized()
    {
        $this->service->expects($this->once())
                      ->method('getSerialized');

        $this->extension->renderSerialized();
    }

    public function testRenderStored()
    {
        $data = array();

        $this->service->expects($this->once())
            ->method('render')
            ->with($this->equalTo($data));

        $this->extension->render($data);
    }

    public function testSet()
    {
        $data = array();

        $this->service->expects($this->once())
            ->method('setData')
            ->with($this->equalTo($data));

        $this->extension->set($data);
    }

    public function testRender()
    {
        $this->service->expects($this->once())
            ->method('render');

        $this->extension->render();
    }

    public function testRenderShort()
    {
        $this->service->expects($this->once())
            ->method('render');

        $this->extension->renderShort();
    }

    public function testTokenParserDeclarations()
    {
        $result = $this->extension->getTokenParsers();

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }
}
