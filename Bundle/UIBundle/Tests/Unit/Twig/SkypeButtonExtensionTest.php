<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Twig\SkypeButtonExtension;

class SkypeButtonExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SkypeButtonExtension
     */
    private $extension;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->extension = new SkypeButtonExtension();
    }

    public function testName()
    {
        $this->assertEquals('oro_ui.skype_button', $this->extension->getName());
    }

    /**
     * @dataProvider optionsDataProvider
     * @param string $username
     * @param array $options
     * @param array $expectedOptions
     * @param string $expectedTemplate
     */
    public function testGetSkypeButton($username, $options, $expectedOptions, $expectedTemplate)
    {
        $env = $this->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->once())
            ->method('render')
            ->with($expectedTemplate, $this->anything())
            ->will(
                $this->returnCallback(
                    function ($template, $options) use ($expectedOptions, $username) {
                        \PHPUnit_Framework_TestCase::assertArrayHasKey('name', $options['options']);
                        \PHPUnit_Framework_TestCase::assertEquals(
                            $expectedOptions['name'],
                            $options['options']['name']
                        );
                        \PHPUnit_Framework_TestCase::assertArrayHasKey('participants', $options['options']);
                        \PHPUnit_Framework_TestCase::assertEquals(
                            $expectedOptions['participants'],
                            $options['options']['participants']
                        );
                        \PHPUnit_Framework_TestCase::assertArrayHasKey('element', $options['options']);
                        \PHPUnit_Framework_TestCase::assertContains(
                            'skype_button_' . md5($username),
                            $options['options']['element']
                        );
                        return 'BUTTON_CODE';
                    }
                )
            );
        $this->assertEquals('BUTTON_CODE', $this->extension->getSkypeButton($env, $username, $options));
    }

    public function optionsDataProvider()
    {
        return array(
            array(
                'echo123',
                array(),
                array(
                    'participants' => array('echo123'),
                    'name' => 'call',
                ),
                SkypeButtonExtension::SKYPE_BUTTON_TEMPLATE
            ),
            array(
                'echo123',
                array(
                    'participants' => array('test'),
                    'name' => 'chat',
                    'template' => 'test_template'
                ),
                array(
                    'participants' => array('test'),
                    'name' => 'chat',
                ),
                'test_template'
            )
        );
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertInternalType('array', $functions);
        $this->assertArrayHasKey('skype_button', $functions);
        $this->assertInstanceOf('\Twig_Function_Method', $functions['skype_button']);
    }
}
