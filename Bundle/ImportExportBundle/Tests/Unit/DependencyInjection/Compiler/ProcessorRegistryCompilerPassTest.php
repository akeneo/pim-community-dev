<?php

namespace Oro\Bundle\ImportExportBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler\ProcessorRegistryCompilerPass;
use Symfony\Component\DependencyInjection\Reference;

class ProcessorRegistryCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessorRegistryCompilerPass
     */
    protected $compiler;

    protected function setUp()
    {
        $this->compiler = new ProcessorRegistryCompilerPass();
    }

    /**
     * @dataProvider processDataProvider
     *
     * @param array $taggedProcessorIds
     * @param array $definitionsExpectations
     */
    public function testProcess(array $taggedProcessorIds, array $definitionsExpectations)
    {
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefinition', 'findTaggedServiceIds'))
            ->getMock();

        $containerBuilder->expects($this->at(0))
            ->method('findTaggedServiceIds')
            ->with(ProcessorRegistryCompilerPass::PROCESSOR_TAG)
            ->will($this->returnValue($taggedProcessorIds));

        $registryDefinition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $containerBuilder->expects($this->at(1))
            ->method('getDefinition')
            ->with(ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_SERVICE)
            ->will($this->returnValue($registryDefinition));

        $callIndex = 0;
        foreach ($definitionsExpectations as $expectation) {
            list($method, $withArguments) = $expectation;
            $mock = $registryDefinition->expects($this->at($callIndex++))->method($method);
            call_user_func_array(array($mock, 'with'), $withArguments);
        }

        $this->compiler->process($containerBuilder);
    }

    public function processDataProvider()
    {
        return array(
            'register_processors' => array(
                'taggedProcessorIds' => array(
                    'oro_test.foo_import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'type' => 'import',
                            'entity' => 'FooEntity',
                            'alias' => 'foo_import'
                        )
                    ),
                    'oro_test.foo_export_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'type' => 'export',
                            'entity' => 'FooEntity',
                            'alias' => 'foo_export'
                        )
                    ),
                    'oro_test.bar_import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'type' => 'import',
                            'entity' => 'BarEntity',
                            'alias' => 'bar_import'
                        )
                    ),
                    'oro_test.bar_export_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'type' => 'export',
                            'entity' => 'BarEntity',
                            'alias' => 'bar_export'
                        )
                    ),
                ),
                'definitionsExpectations' => array(
                    array(
                        'addMethodCall',
                        array(
                            'registerProcessor',
                            array(new Reference('oro_test.foo_import_processor'), 'import', 'FooEntity', 'foo_import')
                        ),
                    ),
                    array(
                        'addMethodCall',
                        array(
                            'registerProcessor',
                            array(new Reference('oro_test.foo_export_processor'), 'export', 'FooEntity', 'foo_export')
                        ),
                    ),
                    array(
                        'addMethodCall',
                        array(
                            'registerProcessor',
                            array(new Reference('oro_test.bar_import_processor'), 'import', 'BarEntity', 'bar_import')
                        ),
                    ),
                    array(
                        'addMethodCall',
                        array(
                            'registerProcessor',
                            array(new Reference('oro_test.bar_export_processor'), 'export', 'BarEntity', 'bar_export')
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider processFailsDataProvider
     *
     * @param array $taggedProcessorIds
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testProcessFails(
        array $taggedProcessorIds,
        $expectedException,
        $expectedExceptionMessage = null
    ) {
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefinition', 'findTaggedServiceIds'))
            ->getMock();

        $containerBuilder->expects($this->atLeastOnce())
            ->method('findTaggedServiceIds')
            ->with(ProcessorRegistryCompilerPass::PROCESSOR_TAG)
            ->will($this->returnValue($taggedProcessorIds));

        $containerBuilder->expects($this->never())->method('getDefinition');

        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->compiler->process($containerBuilder);
    }

    public function processFailsDataProvider()
    {
        return array(
            'type attribute required' => array(
                'taggedProcessorIds' => array(
                    'oro_test.foo_import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'alias' => 'foo_import',
                            'entity' => 'FooEntity'
                        )
                    )
                ),
                'Symfony\Component\DependencyInjection\Exception\LogicException',
                // @codingStandardsIgnoreStart
                'Tag "oro_importexport.processor" for service "oro_test.foo_import_processor" must have attribute "type"'
                // @codingStandardsIgnoreEnd
            ),
            'entity attribute required' => array(
                'taggedProcessorIds' => array(
                    'oro_test.foo_import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'type' => 'import',
                            'alias' => 'foo_import'
                        )
                    )
                ),
                'Symfony\Component\DependencyInjection\Exception\LogicException',
                // @codingStandardsIgnoreStart
                'Tag "oro_importexport.processor" for service "oro_test.foo_import_processor" must have attribute "entity"'
                // @codingStandardsIgnoreEnd
            ),
            'alias attribute required' => array(
                'taggedProcessorIds' => array(
                    'oro_test.foo_import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_TAG,
                            'type' => 'import',
                            'entity' => 'FooEntity'
                        )
                    )
                ),
                'Symfony\Component\DependencyInjection\Exception\LogicException',
                // @codingStandardsIgnoreStart
                'Tag "oro_importexport.processor" for service "oro_test.foo_import_processor" must have attribute "alias"'
                // @codingStandardsIgnoreEnd
            ),
        );
    }
}
