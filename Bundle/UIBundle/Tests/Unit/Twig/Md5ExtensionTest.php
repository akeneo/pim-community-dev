<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Twig\Md5Extension;

class Md5ExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Md5Extension
     */
    private $extension;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->extension = new Md5Extension();
    }

    public function testName()
    {
        $this->assertEquals('oro_md5', $this->extension->getName());
    }

    public function testMd5()
    {
        $this->assertEquals("3474851a3410906697ec77337df7aae4", $this->extension->md5("test_string"));
    }

    public function testSetFilters()
    {
        $this->assertArrayHasKey('md5', $this->extension->getFilters());
    }
}
