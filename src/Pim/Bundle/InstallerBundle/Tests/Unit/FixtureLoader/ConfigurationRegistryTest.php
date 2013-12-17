<?php

namespace Pim\Bundle\InstallerBundle\Tests\Ùnit\FixtureLoader;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $propertyAccessor;

    /**
     * @var \Pim\Bundle\InstallerBundle\FixtureLoader\ConfigurationRegistry
     */
    protected $configurationRegistry;

    protected $configuration = array(
        'default' => array(
            'order' => 100,
            'class' => 'default_class',
            'format1' => array(
                'reader'            => 'default_format1_reader',
                'reader_options'    => array('key' => 'default_format1_reader_option'),
                'processor'         => 'default_format1_processor',
                'processor_options' => array('key' => 'default_format1_processor_option'),
            ),
            'format2' => array(
                'reader'            => 'default_format2_reader',
                'reader_options'    => array('key' => 'default_format2_reader_option'),
                'processor'         => 'default_format2_processor',
                'processor_options' => array('key' => 'default_format2_processor_option'),
            ),
        ),
        'entity1' => array(
            'order' => 150,
            'class' => 'entity1_class',
            'format1' => array(
                'reader'            => 'entity1_format1_reader',
                'reader_options'    => array('key' => 'entity1_format1_reader_option'),
                'processor'         => 'entity1_format1_processor',
                'processor_options' => array('key' => 'entity1_format1_processor_option'),
            ),
            'format2' => array(
                'reader'            => 'entity1_format2_reader',
                'reader_options'    => array('key' => 'entity1_format2_reader_option'),
                'processor'         => 'entity1_format2_processor',
                'processor_options' => array('key' => 'entity1_format2_processor_option'),
            ),
        ),
        'entity2' => array(
            'format2' => array(
                'processor_options' => array('key' => 'entity2_format2_processor_option')
            )
        )
    );

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        $service = new \stdClass;
                        $service->id = $id;

                        return $service;
                    }
                )
            );

        $this->propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $this->propertyAccessor->expects($this->any())
            ->method('setValue')
            ->will(
                $this->returnCallback(
                    function ($object, $propertyPath, $value) {
                        $object->$propertyPath = $value;
                    }
                )
            );

            $this->configurationRegistry = $this
                ->getMockBuilder('Pim\Bundle\InstallerBundle\FixtureLoader\ConfigurationRegistry')
                ->setMethods(array('getConfiguration'))
                ->setConstructorArgs(array($this->container, $this->propertyAccessor, array()))
                ->getMock();

            $this->configurationRegistry->expects($this->any())
                ->method('getConfiguration')
                ->will($this->returnValue($this->configuration));
    }

    public function testContains()
    {
        $this->assertTrue($this->configurationRegistry->contains('entity1'));
        $this->assertTrue($this->configurationRegistry->contains('entity2'));
        $this->assertFalse($this->configurationRegistry->contains('entity3'));
    }

    public function testGetOrder()
    {
        $this->assertEquals(150, $this->configurationRegistry->getOrder('entity1'));
        $this->assertEquals(100, $this->configurationRegistry->getOrder('entity2'));
    }

    public function testGetClass()
    {
        $this->assertEquals('entity1_class', $this->configurationRegistry->getClass('entity1'));
        $this->assertEquals('default_class', $this->configurationRegistry->getClass('entity2'));
    }

    public function testGetProcessor()
    {
        $entity1Format1Processor = $this->configurationRegistry->getProcessor('entity1', 'format1');
        $this->assertEquals('entity1_format1_processor', $entity1Format1Processor->id);
        $this->assertEquals('entity1_format1_processor_option', $entity1Format1Processor->key);

        $entity1Format2Processor = $this->configurationRegistry->getProcessor('entity1', 'format2');
        $this->assertEquals('entity1_format2_processor', $entity1Format2Processor->id);
        $this->assertEquals('entity1_format2_processor_option', $entity1Format2Processor->key);

        $entity2Format1Processor = $this->configurationRegistry->getProcessor('entity2', 'format1');
        $this->assertEquals('default_format1_processor', $entity2Format1Processor->id);
        $this->assertEquals('default_format1_processor_option', $entity2Format1Processor->key);

        $entity2Format2Processor = $this->configurationRegistry->getProcessor('entity2', 'format2');
        $this->assertEquals('default_format2_processor', $entity2Format2Processor->id);
        $this->assertEquals('entity2_format2_processor_option', $entity2Format2Processor->key);
    }

    public function testGetReader()
    {
        $entity1Format1Reader = $this->configurationRegistry->getReader('entity1', 'format1');
        $this->assertEquals('entity1_format1_reader', $entity1Format1Reader->id);
        $this->assertEquals('entity1_format1_reader_option', $entity1Format1Reader->key);

        $entity1Format2Reader = $this->configurationRegistry->getReader('entity1', 'format2');
        $this->assertEquals('entity1_format2_reader', $entity1Format2Reader->id);
        $this->assertEquals('entity1_format2_reader_option', $entity1Format2Reader->key);

        $entity2Format1Reader = $this->configurationRegistry->getReader('entity2', 'format1');
        $this->assertEquals('default_format1_reader', $entity2Format1Reader->id);
        $this->assertEquals('default_format1_reader_option', $entity2Format1Reader->key);

        $entity2Format2Reader = $this->configurationRegistry->getReader('entity2', 'format2');
        $this->assertEquals('default_format2_reader', $entity2Format2Reader->id);
        $this->assertEquals('default_format2_reader_option', $entity2Format2Reader->key);
    }
}
