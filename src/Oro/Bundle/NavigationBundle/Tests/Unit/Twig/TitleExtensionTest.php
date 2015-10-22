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
        $expectedResult = 'expected';

        $this->service->expects($this->at(0))
            ->method('setData')
            ->will($this->returnSelf());

        $this->service->expects($this->at(1))->method('getSerialized')->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->renderSerialized());
    }

    public function testRender()
    {
        $expectedResult = 'expected';
        $title = 'title';

        $this->service->expects($this->at(0))
            ->method('setData')
            ->with(array())
            ->will($this->returnSelf());

        $this->service->expects($this->at(1))
            ->method('render')
            ->with(array(), $title, null, null, true)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->render($title));
    }

    public function testRenderShort()
    {
        $expectedResult = 'expected';
        $title = 'title';

        $this->service->expects($this->at(0))
            ->method('setData')
            ->with(array())
            ->will($this->returnSelf());

        $this->service->expects($this->at(1))
            ->method('render')
            ->with(array(), $title, null, null, true, true)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->renderShort($title));
    }

    /**
     * @dataProvider renderAfterSetDataProvider
     * @param array $data
     * @param array $expectedData
     */
    public function testRenderAfterSet(array $data, array $expectedData)
    {
        foreach ($data as $arguments) {
            list($data, $templateScope) = array_pad($arguments, 2, null);
            $this->extension->set($data, $templateScope);
        }

        $expectedResult = 'expected';
        $title = 'test';

        $this->service->expects($this->at(0))
            ->method('setData')
            ->with($expectedData)
            ->will($this->returnSelf());

        $this->service->expects($this->at(1))
            ->method('render')
            ->with(array(), $title, null, null, true)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->render($title));
    }

    public function renderAfterSetDataProvider()
    {
        return array(
            'override options in same template' => array(
                array(
                    array(array('k1' => 'v1')),
                    array(array('k1' => 'v2')),
                    array(array('k2' => 'v3')),
                ),
                array('k1' => 'v2', 'k2' => 'v3'),
            ),
            'override options in different template' => array(
                array(
                    array(array('k1' => 'v1'), 'child_template'),
                    array(array('k1' => 'v2'), 'child_template'),
                    array(array('k3' => 'v3'), 'child_template'),
                    array(array('k1' => 'v4'), 'parent_template'),
                    array(array('k2' => 'v5'), 'parent_template'),
                    array(array('k3' => 'v6'), 'parent_template'),
                    array(array('k4' => 'v7'), 'parent_template'),
                ),
                array('k1' => 'v2', 'k2' => 'v5', 'k3' => 'v3', 'k4' => 'v7'),
            ),
            'empty data' => array(
                array(),
                array(),
            ),
        );
    }

    public function testSet()
    {
        $fooData = array('k' => 'foo');
        $barData = array('k' => 'bar');

        $this->service->expects($this->never())->method('setData');

        $this->extension->set($fooData);
        $this->extension->set($barData);

        $this->assertAttributeEquals(
            array(
                md5(__FILE__) => array($fooData, $barData)
            ),
            'templateFileTitleDataStack',
            $this->extension
        );
    }

    public function testTokenParserDeclarations()
    {
        $result = $this->extension->getTokenParsers();

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }
}
