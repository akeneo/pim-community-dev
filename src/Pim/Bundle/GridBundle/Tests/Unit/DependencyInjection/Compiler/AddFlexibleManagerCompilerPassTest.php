<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection\Compiler\AbstractCompilerPassTest;
use Pim\Bundle\GridBundle\DependencyInjection\Compiler\AddFlexibleManagerCompilerPass;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddFlexibleManagerCompilerPassTest extends AbstractCompilerPassTest
{
    /**
     * @var AddFlexibleManagerCompilerPass
     */
    private $compiler;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        $this->compiler = new AddFlexibleManagerCompilerPass();
        $this->containerBuilder = new ContainerBuilder();
    }

    /**
     * @dataProvider processDataProvider
     *
     * @param array $containerData
     * @param array $expectedDefinitions
     */
    public function testProcess(
        array $containerData,
        array $expectedDefinitions = array()
    ) {
        $this->addDataToContainerBuilder($this->containerBuilder, $containerData);
        $this->compiler->process($this->containerBuilder);
        $this->assertContainerBuilderHasExpectedDefinitions($this->containerBuilder, $expectedDefinitions);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        $definitionWithSetter = $this->createStubDefinitionWithTags(
            array(
                AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG => array(
                    'name' => AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG
                )
            )
        );
        $definitionWithSetter->addMethodCall('setFlexibleManager', array(new Reference('flexible_manager_id')));

        return array(
            'Not flexible' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG
                                )
                            )
                        )
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'noMethodCalls' => array('setFlexibleManager')
                    )
                )
            ),
            'Has setFlexibleManager method' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $definitionWithSetter
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setFlexibleManager' => array(new Reference('flexible_manager_id'))
                        )
                    )
                )
            ),
            'Has flexible_manager attribute' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG,
                                    AddFlexibleManagerCompilerPass::FLEXIBLE_MANAGER_ATTRIBUTE
                                        => 'flexible_manager_service'
                                )
                            )
                        )
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setFlexibleManager' => array(new Reference('flexible_manager_service'))
                        )
                    )
                )
            ),
            'Has flexible attribute' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG,
                                    AddFlexibleManagerCompilerPass::FLEXIBLE_ATTRIBUTE => true,
                                    AddFlexibleManagerCompilerPass::ENTITY_NAME_ATTRIBUTE => 'EntityName'
                                )
                            )
                        )
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setFlexibleManager' => array(new Reference('test.user_grid.manager.flexible_manager'))
                        )
                    ),
                    'test.user_grid.manager.flexible_manager' => array(
                        'class' => AddFlexibleManagerCompilerPass::FLEXIBLE_MANAGER_CLASS,
                        'factoryService' => AddFlexibleManagerCompilerPass::FLEXIBLE_MANAGER_FACTORY_KEY,
                        'factoryMethod' => AddFlexibleManagerCompilerPass::FLEXIBLE_MANAGER_FACTORY_METHOD,
                        'arguments' => array(
                            'EntityName'
                        )
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider processErrorDataProvider
     *
     * @param array  $containerData
     * @param string $exceptionName
     * @param string $exceptionMessage
     */
    public function testProcessError(array $containerData, $exceptionName, $exceptionMessage)
    {
        $this->addDataToContainerBuilder($this->containerBuilder, $containerData);
        $this->setExpectedException(
            $exceptionName,
            $exceptionMessage
        );
        $this->compiler->process($this->containerBuilder);
    }

    /**
     * @return array
     */
    public function processErrorDataProvider()
    {
        return array(
            'Attribute "entity_name" is required' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test_service' => $this->createStubDefinitionWithTags(
                            array(
                                AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG,
                                    AddFlexibleManagerCompilerPass::FLEXIBLE_ATTRIBUTE => true,
                                )
                            )
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidDefinitionException',
                sprintf(
                    'Definition of service "test_service" must have "entity_name" attribute in tag "%s"',
                    AddFlexibleManagerCompilerPass::DATAGRID_MANAGER_TAG
                )
            )
        );
    }
}
