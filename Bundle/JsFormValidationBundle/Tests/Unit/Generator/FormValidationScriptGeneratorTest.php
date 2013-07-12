<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit\Generator;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\MetadataFactoryInterface;

use Oro\Bundle\JsFormValidationBundle\Generator\FormValidationScriptGenerator;

class FormValidationScriptGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    protected $entityManager;

    /**
     * @var FormValidationScriptGenerator
     */
    protected $generator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    protected $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MetadataFactoryInterface
     */
    protected $metadataFactory;

    protected function setUp()
    {
        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $managerRegistry->expects($this->once())
            ->method('getManager')->with()->will($this->returnValue($this->entityManager));

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->expects($this->once())
            ->method('get')->with('doctrine')->will($this->returnValue($managerRegistry));

        $this->metadataFactory = $this->getMock('Symfony\Component\Validator\MetadataFactoryInterface');
        $this->generator = new FormValidationScriptGenerator($this->container, $this->metadataFactory);
    }

    public function testParentConstructor()
    {
        $this->assertAttributeEquals($this->entityManager, 'em', $this->generator);
    }

    public function testGetClassMetadata()
    {
        $className = 'Foo';
        $metadata = $this->getMockBuilder('Symfony\Component\Validator\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        $this->assertSame($metadata, $this->generator->getClassMetadata($className));
        $this->assertSame($metadata, $this->generator->getClassMetadata($className));
    }
}
