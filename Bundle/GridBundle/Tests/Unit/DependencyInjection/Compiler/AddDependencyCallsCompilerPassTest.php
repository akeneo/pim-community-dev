<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Oro\Bundle\GridBundle\DependencyInjection\OroGridExtension;

class AddDependencyCallsCompilerPassTest extends AbstractCompilerPassTest
{
    /**
     * @var AddDependencyCallsCompilerPass
     */
    private $compiler;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        $this->compiler = new AddDependencyCallsCompilerPass();
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            'Default tag attributes' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'datagrid_name' => 'users',
                                    'route_name' => 'user_grid_route'
                                )
                            )
                        ),
                        AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                            => $this->createStubDefinition('DatagridManagerRegistry'),
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setName' => array('users'),
                            'setQueryFactory' => array(
                                new Reference('test.user_grid.manager.default_query_factory')
                            ),
                            'setRouteGenerator' => array(
                                new Reference('test.user_grid.manager.route.default_generator')
                            ),
                            'setParameters' => array(new Reference('test.user_grid.manager.parameters.default')),
                            'setDatagridBuilder' => array(new Reference('oro_grid.builder.datagrid')),
                            'setListBuilder' => array(new Reference('oro_grid.builder.list')),
                            'setTranslator' => array(new Reference('translator')),
                            'setValidator' => array(new Reference('validator')),
                            'setRouter' => array(new Reference('router')),
                            'setTranslationDomain'
                                => array(new Parameter(OroGridExtension::PARAMETER_TRANSLATION_DOMAIN)),
                        )
                    ),
                    'test.user_grid.manager.default_query_factory' => array(
                        'class' => '%oro_grid.orm.query_factory.query.class%',
                        'arguments' => array()
                    ),
                    'test.user_grid.manager.route.default_generator' => array(
                        'class' => '%oro_grid.route.default_generator.class%',
                        'arguments' => array(
                            new Reference('router'),
                            'user_grid_route'
                        )
                    ),
                    'test.user_grid.manager.parameters.default' => array(
                        'class' => '%oro_grid.datagrid.parameters.class%',
                        'arguments' => array(
                            new Reference('service_container'),
                            'users'
                        )
                    ),
                    AddDependencyCallsCompilerPass::REGISTRY_SERVICE => array(
                        'methodCalls' => array(
                            'addDatagridManagerService' => array('users', 'test.user_grid.manager'),
                        ),
                    ),
                )
            ),
            'Optional tag attribute "query_entity_alias"' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'datagrid_name' => 'users',
                                    'entity_name' => 'User',
                                    'query_entity_alias' => 'u',
                                    'route_name' => 'user_grid_route'
                                )
                            )
                        ),
                        AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                            => $this->createStubDefinition('DatagridManagerRegistry'),
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setQueryFactory' => array(
                                new Reference('test.user_grid.manager.default_query_factory')
                            )
                        )
                    ),
                    'test.user_grid.manager.default_query_factory' => array(
                        'class' => '%oro_grid.orm.query_factory.entity.class%',
                        'arguments' => array(new Reference('doctrine'), 'User', 'u')
                    ),
                    AddDependencyCallsCompilerPass::REGISTRY_SERVICE => array(
                        'methodCalls' => array(
                            'addDatagridManagerService' => array('users', 'test.user_grid.manager'),
                        ),
                    ),
                )
            ),
            'Optional tag attribute "entity_hint"' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'datagrid_name' => 'users',
                                    'entity_name' => 'User',
                                    'entity_hint' => 'users',
                                    'route_name' => 'user_grid_route'
                                )
                            )
                        ),
                        AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                            => $this->createStubDefinition('DatagridManagerRegistry'),
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setEntityHint' => array('users')
                        )
                    ),
                    AddDependencyCallsCompilerPass::REGISTRY_SERVICE => array(
                        'methodCalls' => array(
                            'addDatagridManagerService' => array('users', 'test.user_grid.manager'),
                        ),
                    ),
                )
            ),
            'Tag attributes override services' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test.user_grid.manager' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'datagrid_name' => 'users',
                                    'query_factory' => 'query_factory_service',
                                    'route_generator' => 'route_generator_service',
                                    'datagrid_builder' => 'datagrid_builder_service',
                                    'list_builder' => 'list_builder_service',
                                    'parameters' => 'parameters_service',
                                    'translator' => 'translator_service',
                                    'validator' => 'validator_service',
                                    'router' => 'router_service',
                                    'translation_domain' => 'translation_domain_parameter'
                                )
                            )
                        ),
                        AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                            => $this->createStubDefinition('DatagridManagerRegistry'),
                    )
                ),
                'expectedDefinitions' => array(
                    'test.user_grid.manager' => array(
                        'methodCalls' => array(
                            'setQueryFactory' => array(new Reference('query_factory_service')),
                            'setRouteGenerator' => array(new Reference('route_generator_service')),
                            'setDatagridBuilder' => array(new Reference('datagrid_builder_service')),
                            'setListBuilder' => array(new Reference('list_builder_service')),
                            'setParameters' => array(new Reference('parameters_service')),
                            'setTranslator' => array(new Reference('translator_service')),
                            'setValidator' => array(new Reference('validator_service')),
                            'setRouter' => array(new Reference('router_service')),
                            'setTranslationDomain' => array(new Parameter('translation_domain_parameter')),
                        )
                    ),
                    AddDependencyCallsCompilerPass::REGISTRY_SERVICE => array(
                        'methodCalls' => array(
                            'addDatagridManagerService' => array('users', 'test.user_grid.manager'),
                        ),
                    ),
                )
            )
        );
    }

    /**
     * @dataProvider processErrorDataProvider
     *
     * @param array $containerData
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
            'Manager registry is required' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test_service' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'entity_name' => 'User',
                                    'route_name' => 'user_grid_route',
                                )
                            )
                        ),
                    )
                ),
                'Symfony\Component\DependencyInjection\Exception\InvalidArgumentException',
                sprintf(
                    'The service definition "%s" does not exist.',
                    AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                )
            ),
            'Attribute "datagrid_name" is required' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test_service' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'entity_name' => 'User',
                                    'route_name' => 'user_grid_route',
                                )
                            )
                        ),
                        AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                            => $this->createStubDefinition('DatagridManagerRegistry'),
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidDefinitionException',
                sprintf(
                    'Definition of service "test_service" must have "datagrid_name" attribute in tag "%s"',
                    AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG
                )
            ),
            'Attribute "route_name" is required' => array(
                'containerData' => array(
                    'definitions' => array(
                        'test_service' => $this->createStubDefinitionWithTags(
                            array(
                                AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG => array(
                                    'name' => AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG,
                                    'datagrid_name' => 'users',
                                    'entity_name' => 'User'
                                ),
                            )
                        ),
                        AddDependencyCallsCompilerPass::REGISTRY_SERVICE
                            => $this->createStubDefinition('DatagridManagerRegistry'),
                    ),
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidDefinitionException',
                sprintf(
                    'Definition of service "test_service" must have "route_name" attribute in tag "%s"',
                    AddDependencyCallsCompilerPass::DATAGRID_MANAGER_TAG
                )
            ),
        );
    }
}
