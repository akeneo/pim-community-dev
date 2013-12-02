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
        $type = ProcessorRegistry::TYPE_IMPORT;
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->assertAttributeEquals(
            array($type => array($alias => $processor)),
            'processors',
            $this->registry
        );
        $this->assertAttributeEquals(
            array($entityName => array($type => array($alias => $processor))),
            'processorsByEntity',
            $this->registry
        );

        return $this->registry;
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\LogicException
     * @expectedExceptionMessage Processor with type "import" and alias "entity_name" already exists
     */
    public function testRegisterProcessorFails()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
    }

    public function testUnregisterProcessor()
    {
        $fooType = ProcessorRegistry::TYPE_IMPORT;
        $fooEntityName = 'foo_entity_name';
        $fooAlias = 'foo_processor_alias';
        $fooProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $barType = ProcessorRegistry::TYPE_EXPORT;
        $barEntityName = 'bar_entity_name';
        $barAlias = 'bar_processor_alias';
        $barProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($fooProcessor, $fooType, $fooEntityName, $fooAlias);
        $this->registry->registerProcessor($barProcessor, $barType, $barEntityName, $barAlias);
        $this->registry->unregisterProcessor($fooType, $fooEntityName, $fooAlias);
        $this->assertAttributeEquals(
            array($fooType => array(), $barType => array($barAlias => $barProcessor)),
            'processors',
            $this->registry
        );
        $this->assertAttributeEquals(
            array(
                $fooEntityName => array($fooType => array()),
                $barEntityName => array($barType => array($barAlias => $barProcessor)),
            ),
            'processorsByEntity',
            $this->registry
        );
    }

    public function testHasProcessor()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->assertFalse($this->registry->hasProcessor($type, $alias));
        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->assertTrue($this->registry->hasProcessor($type, $alias));
    }

    public function testGetProcessor()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
        $entityName = 'entity_name';
        $alias = 'processor_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);
        $this->assertSame($processor, $this->registry->getProcessor($type, $alias));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException
     * @expectedExceptionMessage Processor with type "import" and alias "processor_alias" is not exist
     */
    public function testGetProcessorFails()
    {
        $this->registry->getProcessor('import', 'processor_alias');
    }

    public function testGetProcessorsByEntity()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
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

    public function testGetProcessorsByEntityUnknown()
    {
        $this->assertEquals(
            array(),
            $this->registry->getProcessorsByEntity('unknown', 'unknown')
        );
    }

    public function testGetProcessorAliasesByEntityUnknown()
    {
        $this->assertEquals(
            array(),
            $this->registry->getProcessorAliasesByEntity('unknown', 'unknown')
        );
    }

    public function testGetProcessorAliasesByEntity()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
        $entityName = 'entity_name';
        $fooAlias = 'foo_alias';
        $fooProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');
        $barAlias = 'bar_alias';
        $barProcessor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($fooProcessor, $type, $entityName, $fooAlias);
        $this->registry->registerProcessor($barProcessor, $type, $entityName, $barAlias);

        $this->assertEquals(
            array($fooAlias, $barAlias),
            $this->registry->getProcessorAliasesByEntity($type, $entityName)
        );
    }

    public function testGetProcessorEntityName()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
        $entityName = 'entity_name';
        $alias = 'foo_alias';
        $processor = $this->getMock('Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface');

        $this->registry->registerProcessor($processor, $type, $entityName, $alias);

        $this->assertEquals($entityName, $this->registry->getProcessorEntityName($type, $alias));
    }

    /**
     * @expectedException \Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException
     * @expectedExceptionMessage Processor with type "import" and alias "foo_alias" is not exist
     */
    public function testGetProcessorEntityNameFails()
    {
        $type = ProcessorRegistry::TYPE_IMPORT;
        $alias = 'foo_alias';
        $this->registry->getProcessorEntityName($type, $alias);
    }
}
