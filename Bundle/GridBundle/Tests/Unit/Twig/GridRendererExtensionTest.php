<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Twig;

use Oro\Bundle\GridBundle\Twig\GridRendererExtension;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Oro\Bundle\GridBundle\Datagrid\DatagridView;

class GridRendererExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GridRendererExtension
     */
    protected $extension;

    /**
     * @var GridRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $renderer;

    protected function setUp()
    {
        $this->renderer = $this->getMockBuilder('Oro\Bundle\GridBundle\Renderer\GridRenderer')
            ->setMethods(array('getResultsJson'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new GridRendererExtension($this->renderer);
    }

    public function testGetFunctions()
    {
        $expectedResult = array(
            'oro_grid_render_results_json' => new \Twig_Function_Method(
                $this->extension,
                'renderResultsJson',
                array('is_safe' => array('html'))
            ),
        );
        $this->assertEquals($expectedResult, $this->extension->getFunctions());
    }

    public function testRenderResultsJson()
    {
        $datagridView = $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\DatagridView')
            ->disableOriginalConstructor()
            ->getMock();

        $expectedResult = '{data:[]}';
        $this->renderer->expects($this->once())->method('getResultsJson')
            ->with($datagridView)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->renderResultsJson($datagridView));
    }

    public function testGetName()
    {
        $this->assertEquals(GridRendererExtension::NAME, $this->extension->getName());
    }
}
