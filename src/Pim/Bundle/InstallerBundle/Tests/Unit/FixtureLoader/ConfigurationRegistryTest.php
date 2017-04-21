<?php

namespace Pim\Bundle\InstallerBundle\Tests\Unit\FixtureLoader;

use org\bovigo\vfs\vfsStream;

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

    protected $configuration = [
        'default' => [
            'order'   => 100,
            'class'   => 'default_class',
            'format1' => [
                'reader'            => 'default_format1_reader',
                'reader_options'    => ['key' => 'default_format1_reader_option'],
                'processor'         => 'default_format1_processor',
                'processor_options' => ['key' => 'default_format1_processor_option'],
            ],
            'format2' => [
                'reader'            => 'default_format2_reader',
                'reader_options'    => ['key' => 'default_format2_reader_option'],
                'processor'         => 'default_format2_processor',
                'processor_options' => ['key' => 'default_format2_processor_option'],
            ],
        ],
        'entity1' => [
            'order'   => 150,
            'class'   => 'entity1_class',
            'format1' => [
                'reader'            => 'entity1_format1_reader',
                'reader_options'    => ['key' => 'entity1_format1_reader_option'],
                'processor'         => 'entity1_format1_processor',
                'processor_options' => ['key' => 'entity1_format1_processor_option'],
            ],
            'format2' => [
                'reader'            => 'entity1_format2_reader',
                'reader_options'    => ['key' => 'entity1_format2_reader_option'],
                'processor'         => 'entity1_format2_processor',
                'processor_options' => ['key' => 'entity1_format2_processor_option'],
            ],
        ],
        'entity1.step2' => [
            'order'     => 90,
            'file_name' => 'entity1',
            'class'     => 'entity1_class',
            'format1'   => [
                'reader'            => 'entity1_format1_reader2',
                'reader_options'    => ['key' => 'entity1_format1_reader_option2'],
                'processor'         => 'entity1_format1_processor2',
                'processor_options' => ['key' => 'entity1_format1_processor_option2'],
            ],
            'format2' => [
                'reader'            => 'entity1_format2_reader2',
                'reader_options'    => ['key' => 'entity1_format2_reader_option2'],
                'processor'         => 'entity1_format2_processor2',
                'processor_options' => ['key' => 'entity1_format2_processor_option2'],
            ],
        ],
        'entity2' => [
            'format2' => [
                'processor_options' => ['key' => 'entity2_format2_processor_option']
            ]
        ]
    ];

    protected function setUp()
    {
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        $service = new \stdClass();
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
            ->setMethods(['getConfiguration'])
            ->setConstructorArgs([$this->container, $this->propertyAccessor, [], '', false])
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

    public function testGetFixtures()
    {
        $root = vfsStream::setup('root/tmp');
        $filePaths = [
            $root->url() . '/tmp/entity1.format1',
            $root->url() . '/tmp/entity1.format2',
            $root->url() . '/tmp/entity2.format2',
        ];
        array_map('touch', $filePaths);

        $this->assertEquals(
            [
                [
                    'name'      => 'entity1.step2',
                    'extension' => 'format1',
                    'path'      => 'vfs://root/tmp/entity1.format1'
                ],
                [
                    'name'      => 'entity1.step2',
                    'extension' => 'format2',
                    'path'      => 'vfs://root/tmp/entity1.format2'
                ],
                [
                    'name'      => 'entity2',
                    'extension' => 'format2',
                    'path'      => 'vfs://root/tmp/entity2.format2'
                ],
                [
                    'name'      => 'entity1',
                    'extension' => 'format1',
                    'path'      => 'vfs://root/tmp/entity1.format1'
                ],
                [
                    'name'      => 'entity1',
                    'extension' => 'format2',
                    'path'      => 'vfs://root/tmp/entity1.format2'
                ],
            ],
            $this->configurationRegistry->getFixtures($filePaths)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Error, not a file: "/tmp/entity1.format1"
     */
    public function testThrowException()
    {
        $this->configurationRegistry->getFixtures(
            [
                '/tmp/entity1.format1',
            ]
        );
    }
}
