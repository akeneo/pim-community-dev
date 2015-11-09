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
    }

    public function testNameConfigured()
    {
        $this->assertInternalType('string', $this->extension->getName());
    }

    public function testRender()
    {
        $expectedResult = 'expected';
        $title = 'title';

        $this->service->expects($this->at(0))
            ->method('setData')
            ->with([])
            ->will($this->returnSelf());

        $this->service->expects($this->at(1))
            ->method('render')
            ->with([], $title, null, null, true)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->render($title));
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
            ->with([], $title, null, null, true)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->extension->render($title));
    }

    public function renderAfterSetDataProvider()
    {
        return [
            'override options in same template' => [
                [
                    [['k1' => 'v1']],
                    [['k1' => 'v2']],
                    [['k2' => 'v3']],
                ],
                ['k1' => 'v2', 'k2' => 'v3'],
            ],
            'override options in different template' => [
                [
                    [['k1' => 'v1'], 'child_template'],
                    [['k1' => 'v2'], 'child_template'],
                    [['k3' => 'v3'], 'child_template'],
                    [['k1' => 'v4'], 'parent_template'],
                    [['k2' => 'v5'], 'parent_template'],
                    [['k3' => 'v6'], 'parent_template'],
                    [['k4' => 'v7'], 'parent_template'],
                ],
                ['k1' => 'v2', 'k2' => 'v5', 'k3' => 'v3', 'k4' => 'v7'],
            ],
            'empty data' => [
                [],
                [],
            ],
        ];
    }

    public function testSet()
    {
        $fooData = ['k' => 'foo'];
        $barData = ['k' => 'bar'];

        $this->service->expects($this->never())->method('setData');

        $this->extension->set($fooData);
        $this->extension->set($barData);

        $this->assertAttributeEquals(
            [
                md5(__FILE__) => [$fooData, $barData]
            ],
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
