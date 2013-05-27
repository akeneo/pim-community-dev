<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;

class AddFilterTypeCompilerPassTest extends AbstractCompilerPassTest
{
    /**
     * @var AddFilterTypeCompilerPass
     */
    private $compiler;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        $this->compiler = new AddFilterTypeCompilerPass();
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
        array $expectedDefinitions
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
            'Collect actions and filters' => array(
                'containerData' => array(
                    'definitions' => array(
                        // Filter factory and filters definitions
                        AddFilterTypeCompilerPass::DATAGRID_FILTER_FACTORY_KEY =>
                            $this->createStubDefinition(null, range(1, 2)),
                        'oro_grid.orm.filter.type.string' => $this->createStubDefinitionWithTags(
                            array(
                                AddFilterTypeCompilerPass::DATAGRID_FILTER_TAG => array(
                                    'name' => AddFilterTypeCompilerPass::DATAGRID_FILTER_TAG,
                                    'alias' => 'oro_grid_orm_string',
                                )
                            )
                        ),
                        // Action factory and actions definitions
                        AddFilterTypeCompilerPass::DATAGRID_ACTION_FACTORY_KEY =>
                            $this->createStubDefinition(null, range(1, 2)),
                        'oro_grid.action.type.redirect' => $this->createStubDefinitionWithTags(
                            array(
                                AddFilterTypeCompilerPass::DATAGRID_ACTION_TAG => array(
                                    'name' => AddFilterTypeCompilerPass::DATAGRID_ACTION_TAG,
                                    'alias' => 'oro_grid_action_redirect',
                                )
                            )
                        ),
                        'oro_grid.action.type.delete' => $this->createStubDefinitionWithTags(
                            array(
                                AddFilterTypeCompilerPass::DATAGRID_ACTION_TAG => array(
                                    'name' => AddFilterTypeCompilerPass::DATAGRID_ACTION_TAG,
                                    'alias' => 'oro_grid_action_delete',
                                )
                            )
                        ),
                        'oro_grid.action.type.edit' => $this->createStubDefinitionWithTags(
                            array(
                                AddFilterTypeCompilerPass::DATAGRID_ACTION_TAG => array(
                                    'name' => AddFilterTypeCompilerPass::DATAGRID_ACTION_TAG
                                )
                            )
                        )
                    )
                ),
                'expectedDefinitions' => array(
                    // Second argument of filter factory
                    AddFilterTypeCompilerPass::DATAGRID_FILTER_FACTORY_KEY => array(
                        'arguments' => array(
                            1 => array('oro_grid_orm_string' => 'oro_grid.orm.filter.type.string')
                        )
                    ),
                    // Second argument of action factory
                    AddFilterTypeCompilerPass::DATAGRID_ACTION_FACTORY_KEY => array(
                        'arguments' => array(
                            1 => array(
                                'oro_grid_action_redirect' => 'oro_grid.action.type.redirect',
                                'oro_grid_action_delete' => 'oro_grid.action.type.delete',
                                'oro_grid.action.type.edit' => 'oro_grid.action.type.edit'
                            )
                        )
                    ),
                    // Changed scopes of filters and actions services
                    'oro_grid.orm.filter.type.string' => array(
                        'scope' => ContainerInterface::SCOPE_PROTOTYPE
                    ),
                    'oro_grid.action.type.redirect' => array(
                        'scope' => ContainerInterface::SCOPE_PROTOTYPE
                    ),
                    'oro_grid.action.type.delete' => array(
                        'scope' => ContainerInterface::SCOPE_PROTOTYPE
                    ),
                    'oro_grid.action.type.edit' => array(
                        'scope' => ContainerInterface::SCOPE_PROTOTYPE
                    )
                )
            ),
            'Empty tags' => array(
                'containerData' => array(
                    'definitions' => array(
                        // Filter and action factories
                        AddFilterTypeCompilerPass::DATAGRID_FILTER_FACTORY_KEY =>
                            $this->createStubDefinition(null, array(null, array())),
                        AddFilterTypeCompilerPass::DATAGRID_ACTION_FACTORY_KEY =>
                            $this->createStubDefinition(null, array(null, array())),
                        // No services tagged with filter or action tags
                    )
                ),
                // No changes in factories arguments because there were no tagged services
                'expectedDefinitions' => array(
                    AddFilterTypeCompilerPass::DATAGRID_FILTER_FACTORY_KEY => array(
                        'arguments' => array(
                            1 => array()
                        )
                    ),
                    AddFilterTypeCompilerPass::DATAGRID_ACTION_FACTORY_KEY => array(
                        'arguments' => array(
                            1 => array()
                        )
                    )
                )
            )
        );
    }
}
