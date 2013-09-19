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
     * @param array $taggedRegistryIds
     * @param array $taggedProcessorIds
     * @param array $definitionsExpectations
     */
    public function testProcess(array $taggedRegistryIds, array $taggedProcessorIds, array $definitionsExpectations)
    {
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefinition', 'findTaggedServiceIds'))
            ->getMock();

        $index = 0;
        $containerBuilder->expects($this->at($index++))
            ->method('findTaggedServiceIds')
            ->with(ProcessorRegistryCompilerPass::PROCESSOR_TAG)
            ->will($this->returnValue($taggedProcessorIds));
        $containerBuilder->expects($this->at($index++))
            ->method('findTaggedServiceIds')
            ->with(ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG)
            ->will($this->returnValue($taggedRegistryIds));

        foreach ($definitionsExpectations as $serviceId => $expectations) {
            $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
            $containerBuilder->expects($this->at($index++))
                ->method('getDefinition')
                ->with($serviceId)
                ->will($this->returnValue($definition));

            $definitionIndex = 0;
            foreach ($expectations as $expectation) {
                list($method, $withArguments) = $expectation;
                $mock = $definition->expects($this->at($definitionIndex++))->method($method);
                call_user_func_array(array($mock, 'with'), $withArguments);
            }
        }

        $this->compiler->process($containerBuilder);
    }

    public function processDataProvider()
    {
        return array(
            'register_processors' => array(
                'taggedRegistryIds' => array(
                    'oro_importexport.import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG,
                            'type' => 'import'
                        )
                    ),
                    'oro_importexport.export_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG,
                            'type' => 'export'
                        )
                    ),
                ),
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
                            'alias' => 'bar_import'
                        )
                    ),
                ),
                'definitionsExpectations' => array(
                    'oro_importexport.import_processor' => array(
                        array(
                            'addMethodCall',
                            array(
                                'registerProcessor',
                                array(new Reference('oro_test.foo_import_processor'), 'FooEntity', 'foo_import')
                            ),
                            'addMethodCall',
                            array(
                                'registerProcessor',
                                array(new Reference('oro_test.bar_import_processor'), 'BarEntity', 'bar_import')
                            ),
                        )
                    ),
                    'oro_importexport.export_processor' => array(
                        array(
                            'addMethodCall',
                            array(
                                'registerProcessor',
                                array(new Reference('oro_test.foo_export_processor'), 'FooEntity', 'foo_export')
                            ),
                            'addMethodCall',
                            array(
                                'registerProcessor',
                                array(new Reference('oro_test.bar_export_processor'), 'BarEntity', 'bar_export')
                            )
                        )
                    ),
                )
            ),
        );
    }

    /**
     * @dataProvider processFailsDataProvider
     *
     * @param array $taggedRegistryIds
     * @param array $taggedProcessorIds
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testProcessFails(
        array $taggedRegistryIds,
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
            ->will(
                $this->returnValueMap(
                    array(
                        array(ProcessorRegistryCompilerPass::PROCESSOR_TAG, $taggedProcessorIds),
                        array(ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG, $taggedRegistryIds)
                    )
                )
            );

        $containerBuilder->expects($this->never())->method('getDefinition');

        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->compiler->process($containerBuilder);
    }

    public function processFailsDataProvider()
    {
        return array(
            'type attribute required' => array(
                'taggedRegistryIds' => array(
                        'oro_importexport.import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG,
                        )
                    )
                ),
                'taggedProcessorIds' => array(),
                'Symfony\Component\DependencyInjection\Exception\LogicException',
                // @codingStandardsIgnoreStart
                'Tag "oro_importexport.processor_registry" for service "oro_importexport.import_processor" must have attribute "type"'
                // @codingStandardsIgnoreEnd
            ),
            'entity attribute required' => array(
                'taggedRegistryIds' => array(
                        'oro_importexport.import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG,
                            'type' => 'import'
                        )
                    )
                ),
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
                'taggedRegistryIds' => array(
                        'oro_importexport.import_processor' => array(
                        array(
                            'name' => ProcessorRegistryCompilerPass::PROCESSOR_REGISTRY_TAG,
                            'type' => 'import'
                        )
                    )
                ),
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
