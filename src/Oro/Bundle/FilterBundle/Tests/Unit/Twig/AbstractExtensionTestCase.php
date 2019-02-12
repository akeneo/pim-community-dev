<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Twig;

use Oro\Bundle\FilterBundle\Twig\AbstractExtension;

class AbstractExtensionTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing class name
     */
    const TESTING_CLASS = AbstractExtension::class;

    /**#@+
     * Test parameters
     */
    const TEST_TEMPLATE_NAME = 'test_template_name';
    const TEST_BLOCK_HTML = 'test_block_html';
    /**#@-*/

    /**
     * @var AbstractExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = [];

    /**
     * @var array
     */
    protected $expectedFilters = [];

    protected function setUp(): void
    {
        $className = static::TESTING_CLASS;
        $this->extension = new $className(self::TEST_TEMPLATE_NAME);
    }

    protected function tearDown()
    {
        unset($this->extension);
    }

    public function testGetName()
    {
        $className = static::TESTING_CLASS;
        $this->assertEquals($className::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $twigNode = $this->createMock('\Twig_Node');

        $actualFunctions = $this->extension->getFunctions();

        /** @var $function \Twig_SimpleFunction */
        foreach ($actualFunctions as $function) {
            $functionName = $function->getName();
            $this->assertArrayHasKey($functionName, $this->expectedFunctions);

            $expectedParameters = $this->expectedFunctions[$functionName];
            $this->assertEquals([$this->extension, $expectedParameters['callback']], $function->getCallable());
            $this->assertEquals($expectedParameters['safe'], $function->getSafe($twigNode));
            $this->assertEquals($expectedParameters['needs_environment'], $function->needsEnvironment());
        }
    }

    public function testGetFilters()
    {
        $actualFilters = $this->extension->getFilters();

        /** @var $filter \Twig_SimpleFilter */
        foreach ($actualFilters as $filter) {
            $filterName = $filter->getName();
            $this->assertArrayHasKey($filterName, $this->expectedFilters);

            $expectedParameters = $this->expectedFilters[$filterName];
            $this->assertEquals([$this->extension, $expectedParameters['callback']], $filter->getCallable());
        }
    }
}
