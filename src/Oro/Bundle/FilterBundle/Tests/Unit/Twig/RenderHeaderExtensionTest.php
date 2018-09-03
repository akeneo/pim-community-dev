<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Twig;

use Oro\Bundle\FilterBundle\Twig\RenderHeaderExtension;

class RenderHeaderExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Testing class name
     */
    const TESTING_CLASS = RenderHeaderExtension::class;

    /**
     * @var RenderHeaderExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = [
        'oro_filter_render_header_javascript' => [
            'callback'          => 'renderHeaderJavascript',
            'safe'              => ['html'],
            'needs_environment' => true
        ],
        'oro_filter_render_header_stylesheet' => [
            'callback'          => 'renderHeaderStylesheet',
            'safe'              => ['html'],
            'needs_environment' => true
        ],
    ];

    /**
     * Prepares twig environment mock
     *
     * @param string $templateName
     * @param string $blockName
     * @param string $blockHtml
     * @return \Twig_Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareEnvironmentMock($templateName, $blockName, $blockHtml)
    {
        $template = $this->getMockForAbstractClass(
            '\Twig_Template',
            [],
            '',
            false,
            true,
            true,
            ['renderBlock']
        );
        $template->expects($this->once())
            ->method('renderBlock')
            ->with($blockName, [])
            ->will($this->returnValue($blockHtml));

        $environment = $this->createMock('\Twig_Environment', ['loadTemplate']);
        $environment->expects($this->once())
            ->method('loadTemplate')
            ->with($templateName)
            ->will($this->returnValue($template));

        return $environment;
    }

    public function testRenderHeaderJavascript()
    {
        $environment = $this->prepareEnvironmentMock(
            self::TEST_TEMPLATE_NAME,
            RenderHeaderExtension::HEADER_JAVASCRIPT,
            self::TEST_BLOCK_HTML
        );

        $html = $this->extension->renderHeaderJavascript($environment);
        $this->assertEquals(self::TEST_BLOCK_HTML, $html);
    }

    public function testRenderHeaderStylesheet()
    {
        $environment = $this->prepareEnvironmentMock(
            self::TEST_TEMPLATE_NAME,
            RenderHeaderExtension::HEADER_STYLESHEET,
            self::TEST_BLOCK_HTML
        );

        $html = $this->extension->renderHeaderStylesheet($environment);
        $this->assertEquals(self::TEST_BLOCK_HTML, $html);
    }
}
