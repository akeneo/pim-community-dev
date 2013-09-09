<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\DataFixtures;

use Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture;

class AbstractTranslatableEntityFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|AbstractTranslatableEntityFixture
     */
    protected $fixture;

    protected function setUp()
    {
        $this->fixture =
            $this->getMockBuilder('Oro\Bundle\TranslationBundle\DataFixtures\AbstractTranslatableEntityFixture')
                ->setMethods(array('loadEntities'))
                ->getMockForAbstractClass();
    }

    protected function tearDown()
    {
        unset($this->fixture);
    }

    public function testSetContainer()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fixture->setContainer($container);

        $this->assertAttributeEquals($container, 'container', $this->fixture);
    }

    public function testLoad()
    {
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        $container->expects($this->once())
            ->method('get')
            ->with('translator')
            ->will($this->returnValue($translator));

        $objectManager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fixture->expects($this->once())
            ->method('loadEntities')
            ->with($objectManager);

        $this->fixture->setContainer($container);
        $this->fixture->load($objectManager);

        $this->assertAttributeEquals($translator, 'translator', $this->fixture);
    }
}
