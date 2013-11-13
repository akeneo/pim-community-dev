<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action;

use Oro\Bundle\WorkflowBundle\Model\Action\ActionAssembler;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface;
use Oro\Bundle\WorkflowBundle\Model\Action\TreeExecutor;
use Oro\Bundle\WorkflowBundle\Model\Action\ActionFactory;
use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action\Stub\ArrayAction;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\Action\Stub\ArrayCondition;

class ActionAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $source
     * @param array $expected
     *
     * @dataProvider assembleDataProvider
     */
    public function testAssemble(array $source, array $expected)
    {
        $test = $this;

        $actionFactory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\ActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $actionFactory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($type, $options, $condition) use ($test) {
                        if ($type == TreeExecutor::ALIAS) {
                            $action = $test->getTreeExecutorMock();
                        } else {
                            $action = new ArrayAction(array('_type' => $type));
                            $action->initialize($options);
                        }
                        if ($condition) {
                            $action->setCondition($condition);
                        }
                        return $action;
                    }
                )
            );

        $conditionFactory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $conditionFactory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($type, $options) {
                        $condition = new ArrayCondition(array('_type' => $type));
                        $condition->initialize($options);
                        return $condition;
                    }
                )
            );

        $configurationPass = $this->getMockBuilder(
            'Oro\Bundle\WorkflowBundle\Model\ConfigurationPass\ConfigurationPassInterface'
        )->getMockForAbstractClass();

        $configurationPass->expects($this->any())
            ->method('passConfiguration')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function (array $data) {
                        $data['_pass'] = true;
                        return $data;
                    }
                )
            );

        /** @var ActionFactory $actionFactory */
        /** @var ConditionFactory $conditionFactory */
        $assembler = new ActionAssembler($actionFactory, $conditionFactory);
        $assembler->addConfigurationPass($configurationPass);
        /** @var TreeExecutor $actualTree */
        $actualTree = $assembler->assemble($source);
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\Action\TreeExecutor', $actualTree);
        $this->assertEquals($expected, $this->getActions($actualTree));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function assembleDataProvider()
    {
        return array(
            'empty configuration' => array(
                'source'   => array(),
                'expected' => array(),
            ),
            'not empty configuration' => array(
                'source' => array(
                    array(
                        '@create_new_entity' => array(
                            'parameters' => array('class_name' => 'TestClass'),
                        ),
                    ),
                    array(
                        '@assign_value' => array(
                            'parameters' => array('from' => 'name', 'to' => 'contact.name'),
                            'break_on_failure' => true,
                        )
                    ),
                    array('not_a_service' => array())
                ),
                'expected' => array(
                    array(
                        'instance' => array(
                            '_type' => 'create_new_entity',
                            'parameters' => array('class_name' => 'TestClass', '_pass' => true)
                        ),
                        'breakOnFailure' => true,
                    ),
                    array(
                        'instance' => array(
                            '_type' => 'assign_value',
                            'parameters' => array('from' => 'name', 'to' => 'contact.name', '_pass' => true)
                        ),
                        'breakOnFailure' => true,
                    ),
                ),
            ),
            'nested configuration' => array(
                'source' => array(
                    array(
                        '@tree' => array(
                            array(
                                '@assign_value' => array(
                                    'parameters' => array('from' => 'name', 'to' => 'contact.name'),
                                    'break_on_failure' => true,
                                )
                            ),
                        )
                    ),
                    array(
                        '@tree' => array(
                            'actions' => array(
                                array(
                                    '@assign_value' => array(
                                        'parameters' => array('from' => 'date', 'to' => 'contact.date'),
                                        'break_on_failure' => false,
                                    )
                                ),
                            ),
                        )
                    ),
                ),
                'expected' => array(
                    array(
                        'instance' => array(
                            '_type' => 'tree',
                            'post_actions' => array(
                                array(
                                    'instance' => array(
                                        '_type' => 'assign_value',
                                        'parameters' => array('from' => 'name', 'to' => 'contact.name', '_pass' => true)
                                    ),
                                    'breakOnFailure' => true,
                                ),
                            )
                        ),
                        'breakOnFailure' => true,
                    ),
                    array(
                        'instance' => array(
                            '_type' => 'tree',
                            'post_actions' => array(
                                array(
                                    'instance' => array(
                                        '_type' => 'assign_value',
                                        'parameters' => array('from' => 'date', 'to' => 'contact.date', '_pass' => true)
                                    ),
                                    'breakOnFailure' => false,
                                ),
                            )
                        ),
                        'breakOnFailure' => true,
                    ),
                ),
            ),
            'condition configuration' => array(
                'source' => array(
                    array(
                        '@tree' => array(
                            'conditions' => array('@not_empty' => '$contact'),
                            'actions' => array(
                                array(
                                    '@assign_value' => array(
                                        'conditions' => array('@not_empty' => '$contact.foo'),
                                        'parameters' => array('from' => 'name', 'to' => 'contact.foo'),
                                    )
                                ),
                            ),
                            'break_on_failure' => false,
                        )
                    ),
                ),
                'expected' => array(
                    array(
                        'instance' => array(
                            '_type' => 'tree',
                            'post_actions' => array(
                                array(
                                    'instance' => array(
                                        '_type' => 'assign_value',
                                        'parameters' => array('from' => 'name', 'to' => 'contact.foo', '_pass' => true),
                                        'condition' => array('_type' => 'configurable', '@not_empty' => '$contact.foo'),
                                    ),
                                    'breakOnFailure' => true,
                                ),
                            ),
                            'condition' => array(
                                '_type' => 'configurable',
                                '@not_empty' => '$contact'
                            ),
                        ),
                        'breakOnFailure' => false,
                    ),
                ),
            ),
        );
    }

    /**
     * @param TreeExecutor $treeExecutor
     * @param ActionInterface $action
     * @param boolean $breakOnFailure
     */
    public function addPostAction(TreeExecutor $treeExecutor, ActionInterface $action, $breakOnFailure)
    {
        $actionsReflection = $this->getTreeExecutorActionReflection();

        $actionData = array();
        if ($action instanceof TreeExecutor) {
            $actionData = array(
                '_type'        => TreeExecutor::ALIAS,
                'post_actions' => $this->getActions($action),
            );
        } elseif ($action instanceof ArrayAction) {
            $actionData = $action->toArray();
        }

        $conditionData = $this->getCondition($action);
        if ($conditionData) {
            $actionData['condition'] = $conditionData;
        }

        $treeActions = $actionsReflection->getValue($treeExecutor);
        $treeActions[] = array(
            'instance'       => $actionData,
            'breakOnFailure' => $breakOnFailure
        );

        $actionsReflection->setValue($treeExecutor, $treeActions);
    }

    /**
     * @param TreeExecutor $action
     * @return array
     */
    protected function getActions(TreeExecutor $action)
    {
        $actionsReflection = $this->getTreeExecutorActionReflection();

        return $actionsReflection->getValue($action);
    }

    /**
     * @return \ReflectionProperty
     */
    protected function getTreeExecutorActionReflection()
    {
        $reflection = new \ReflectionProperty('Oro\Bundle\WorkflowBundle\Model\Action\TreeExecutor', 'actions');
        $reflection->setAccessible(true);

        return $reflection;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TreeExecutor
     */
    public function getTreeExecutorMock()
    {
        $test = $this;

        $treeExecutor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Action\TreeExecutor')
            ->setMethods(array('addAction'))
            ->getMock();
        $treeExecutor->expects($this->any())
            ->method('addAction')
            ->will(
                $this->returnCallback(
                    function ($action, $breakOnFailure) use ($treeExecutor, $test) {
                        /** @var TreeExecutor $treeExecutor */
                        $test->addPostAction($treeExecutor, $action, $breakOnFailure);
                    }
                )
            );

        return $treeExecutor;
    }

    /**
     * @param ActionInterface $postAction
     * @return array|null
     */
    protected function getCondition(ActionInterface $postAction)
    {
        /** @var ArrayCondition $condition */
        $condition = null;
        if ($postAction instanceof TreeExecutor) {
            $reflection = new \ReflectionProperty(
                'Oro\Bundle\WorkflowBundle\Model\Action\TreeExecutor',
                'condition'
            );
            $reflection->setAccessible(true);
            $condition = $reflection->getValue($postAction);
        } elseif ($postAction instanceof ArrayAction) {
            $condition = $postAction->getCondition();
        }

        return $condition ? $condition->toArray() : null;
    }
}
