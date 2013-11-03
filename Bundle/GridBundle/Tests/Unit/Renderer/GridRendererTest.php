<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Renderer;

use Symfony\Bundle\FrameworkBundle\Templating\PhpEngine;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Oro\Bundle\GridBundle\Datagrid\DatagridView;

class GridRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhpEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $engine;

    /**
     * @var string
     */
    protected $jsonTemplate = 'OroGridBundle:Datagrid:list.json.php';

    /**
     * @var GridRenderer
     */
    protected $renderer;

    protected function setUp()
    {
        $this->engine = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\PhpEngine')
            ->setMethods(array('render', 'renderResponse'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderer = new GridRenderer($this->engine, $this->jsonTemplate);
    }

    public function testGetResultsJson()
    {
        $datagridView = $this->createMockDatagridView();

        $expectedJson = '{data:[]}';

        $this->engine->expects($this->once())
            ->method('render')
            ->with($this->jsonTemplate, array('datagrid' => $datagridView))
            ->will($this->returnValue($expectedJson));

        $this->assertEquals($expectedJson, $this->renderer->getResultsJson($datagridView));
    }

    public function testGetResultsJsonResponse()
    {
        $datagridView = $this->createMockDatagridView();

        $actualResponse = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $expectedResponse = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $this->engine->expects($this->once())
            ->method('renderResponse')
            ->with($this->jsonTemplate, array('datagrid' => $datagridView), $actualResponse)
            ->will($this->returnValue($expectedResponse));

        $this->assertEquals(
            $expectedResponse,
            $this->renderer->renderResultsJsonResponse($datagridView, $actualResponse)
        );
    }

    protected function createMockDatagridView()
    {
        return $this->getMockBuilder('Oro\Bundle\GridBundle\Datagrid\DatagridView')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
