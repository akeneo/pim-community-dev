<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionAssembler;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface;
use Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor;
use Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory;
use Oro\Bundle\WorkflowBundle\Model\Pass\ParameterPass;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub\ArrayPostAction;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Model\PostAction\Stub\ArrayCondition;

class PostActionAssemblerTest extends \PHPUnit_Framework_TestCase
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

        $postActionFactory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $postActionFactory->expects($this->any())
            ->method('create')
            ->will(
                $this->returnCallback(
                    function ($type, $options, $condition) use ($test) {
                        if ($type == TreeExecutor::ALIAS) {
                            $postAction = $test->getTreeExecutorMock();
                        } else {
                            $postAction = new ArrayPostAction(array('_type' => $type));
                            $postAction->initialize($options);
                        }
                        if ($condition) {
                            $postAction->setCondition($condition);
                        }
                        return $postAction;
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

        $pass = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('pass'))
            ->getMockForAbstractClass();
        $pass->expects($this->any())
            ->method('pass')
            ->with($this->isType('array'))
            ->will(
                $this->returnCallback(
                    function (array $data) {
                        $data['_pass'] = true;
                        return $data;
                    }
                )
            );

        /** @var PostActionFactory $postActionFactory */
        /** @var ParameterPass $pass */
        $assembler = new PostActionAssembler($postActionFactory, $conditionFactory, $pass);
        /** @var TreeExecutor $actualTree */
        $actualTree = $assembler->assemble($source);
        $this->assertInstanceOf('Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor', $actualTree);
        $this->assertEquals($expected, $this->getPostActions($actualTree));
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
                            'post_actions' => array(
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
                            'post_actions' => array(
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
     * @param PostActionInterface $postAction
     * @param boolean $breakOnFailure
     */
    public function addPostAction(TreeExecutor $treeExecutor, PostActionInterface $postAction, $breakOnFailure)
    {
        $postActionsReflection = $this->getTreeExecutorPostActionReflection();

        $postActionData = array();
        if ($postAction instanceof TreeExecutor) {
            $postActionData = array(
                '_type'        => TreeExecutor::ALIAS,
                'post_actions' => $this->getPostActions($postAction),
            );
        } elseif ($postAction instanceof ArrayPostAction) {
            $postActionData = $postAction->toArray();
        }

        $conditionData = $this->getCondition($postAction);
        if ($conditionData) {
            $postActionData['condition'] = $conditionData;
        }

        $treePostActions = $postActionsReflection->getValue($treeExecutor);
        $treePostActions[] = array(
            'instance'       => $postActionData,
            'breakOnFailure' => $breakOnFailure
        );

        $postActionsReflection->setValue($treeExecutor, $treePostActions);
    }

    /**
     * @param TreeExecutor $postAction
     * @return array
     */
    protected function getPostActions(TreeExecutor $postAction)
    {
        $postActionsReflection = $this->getTreeExecutorPostActionReflection();

        return $postActionsReflection->getValue($postAction);
    }

    /**
     * @return \ReflectionProperty
     */
    protected function getTreeExecutorPostActionReflection()
    {
        $reflection = new \ReflectionProperty('Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor', 'postActions');
        $reflection->setAccessible(true);

        return $reflection;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TreeExecutor
     */
    public function getTreeExecutorMock()
    {
        $test = $this;

        $treeExecutor = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor')
            ->setMethods(array('addPostAction'))
            ->getMock();
        $treeExecutor->expects($this->any())
            ->method('addPostAction')
            ->will(
                $this->returnCallback(
                    function ($postAction, $breakOnFailure) use ($treeExecutor, $test) {
                        /** @var TreeExecutor $treeExecutor */
                        $test->addPostAction($treeExecutor, $postAction, $breakOnFailure);
                    }
                )
            );

        return $treeExecutor;
    }

    /**
     * @param PostActionInterface $postAction
     * @return array|null
     */
    protected function getCondition(PostActionInterface $postAction)
    {
        /** @var ArrayCondition $condition */
        $condition = null;
        if ($postAction instanceof TreeExecutor) {
            $reflection = new \ReflectionProperty(
                'Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor',
                'condition'
            );
            $reflection->setAccessible(true);
            $condition = $reflection->getValue($postAction);
        } elseif ($postAction instanceof ArrayPostAction) {
            $condition = $postAction->getCondition();
        }

        return $condition ? $condition->toArray() : null;
    }
}
