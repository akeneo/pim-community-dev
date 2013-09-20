<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;

class ProcessorRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessorRegistry
     */
    protected $registry;

    protected function setUp()
    {
        $this->registry = new ProcessorRegistry();
    }

    public function testRegisterProcessor()
    {
        $type = 'import';
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->assertAttributeEquals(
            array($type => array($entityName => array($alias => $processor))),
            'processors',
            $this->registry
        );

        return $this->registry;
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage "import" processor "processor_alias" for entity "entity_name" already exists
     */
    public function testRegisterProcessorFails()
    {
        $type = 'import';
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
    }

    public function testUnregisterProcessor()
    {
        $type = 'import';
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->registry->unregisterProcessor($type, $entityName, $alias);
        $this->assertAttributeEquals(
            array($type => array($entityName => array())),
            'processors',
            $this->registry
        );
    }

    public function testHasProcessor()
    {
        $type = 'import';
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->assertFalse($this->registry->hasProcessor($type, $entityName, $alias));
        $this->assertFalse($this->registry->hasProcessor($type, $entityName));
        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->assertTrue($this->registry->hasProcessor($type, $entityName, $alias));
        $this->assertTrue($this->registry->hasProcessor($type, $entityName));
    }

    public function testGetProcessor()
    {
        $type = 'import';
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->assertSame($processor, $this->registry->getProcessor($type, $entityName, $alias));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException
     * @expectedExceptionMessage "import" processor "processor_alias" for entity "entity_name" is not exist
     */
    public function testGetProcessorFails()
    {
        $this->registry->getProcessor('import', 'entity_name', 'processor_alias');
    }

    public function testGetProcessorsByEntity()
    {
        $type = 'import';
        $entityName = 'entity_name';
        $fooAlias = 'foo_alias';
        $fooProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');
        $barAlias = 'bar_alias';
        $barProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($fooProcessor, $type, $entityName, $fooAlias);
        $this->registry->registerProcessor($barProcessor, $type, $entityName, $barAlias);

        $this->assertEquals(
            array($fooAlias => $fooProcessor, $barAlias => $barProcessor),
            $this->registry->getProcessorsByEntity($type, $entityName)
        );
    }
}
