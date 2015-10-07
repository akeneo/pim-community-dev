<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Twig\CeilExtension;

class CeilExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CeilExtension
     */
    private $extension;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->extension = new CeilExtension();
    }

    public function testName()
    {
        $this->assertEquals('oro_ceil', $this->extension->getName());
    }

    /**
     * @dataProvider provider
     */
    public function testCeil($expected, $testValue)
    {
        $this->assertEquals($expected, $this->extension->ceil($testValue));
    }

    public function provider()
    {
        return array(
            array(5, 4.6),
            array(5, 4.1)
        );
    }

    public function testSetFilters()
    {
        $this->assertArrayHasKey('ceil', $this->extension->getFilters());
    }
}
