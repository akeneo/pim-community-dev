<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Twig;

use Oro\Bundle\TagBundle\Twig\TagExtension;

class TagExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TagExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new TagExtension($this->manager);
    }

    public function tearDown()
    {
        unset($this->manager);
        unset($this->extension);
    }

    public function testName()
    {
        $this->assertEquals('oro_tag', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $this->assertArrayHasKey('oro_tag_get_list', $this->extension->getFunctions());
    }

    public function testGet()
    {
        $entity = $this->getMock('Oro\Bundle\TagBundle\Entity\Taggable');

        $this->manager->expects($this->once())
            ->method('getPreparedArray')
            ->with($entity);

        $this->extension->get($entity);
    }
}
