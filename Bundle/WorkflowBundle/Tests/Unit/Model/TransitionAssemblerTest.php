<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowStepType;
use Oro\Bundle\WorkflowBundle\Model\Transition;
use Oro\Bundle\WorkflowBundle\Model\TransitionAssembler;
use Oro\Bundle\WorkflowBundle\Model\Condition\Configurable as ConfigurableCondition;
use Oro\Bundle\WorkflowBundle\Model\PostAction\Configurable as ConfigurablePostAction;

class TransitionAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $conditionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $postActionFactory;

    /**
     * @var TransitionAssembler
     */
    protected $assembler;

    /**
     * @var array
     */
    protected $transitionDefinitions = array(
        'empty_definition' => array(),
        'with_condition' => array(
            'conditions' => array('@true' => null)
        ),
        'with_post_actions' => array(
            'post_actions' => array('@assign_value' => array('parameters' => array('$attribute', 'first_value')))
        ),
        'full_definition' => array(
            'conditions' => array('@true' => null),
            'post_actions' => array('@assign_value' => array('parameters' => array('$attribute', 'first_value')))
        )
    );

    protected function setUp()
    {
        $this->conditionFactory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->postActionFactory = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->assembler = new TransitionAssembler($this->conditionFactory, $this->postActionFactory);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @dataProvider missedTransitionDefinitionDataProvider
     * @param array $configuration
     */
    public function testAssembleNoRequiredTransitionDefinitionException($configuration)
    {
        $this->assembler->assemble($configuration, array(), array());
    }

    public function missedTransitionDefinitionDataProvider()
    {
        return array(
            'no options' => array(
                array(
                    'name' => array()
                )
            ),
            'no transition_definition' => array(
                array(
                    'name' => array(
                        '' => 'test'
                    )
                )
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @dataProvider incorrectTransitionDefinitionDataProvider
     * @param array $definitions
     */
    public function testUnknownTransitionDefinitionAssembler($definitions)
    {
        $configuration = array(
            'test' => array(
                'transition_definition' => 'unknown'
            )
        );
        $this->assembler->assemble($configuration, $definitions, array());
    }

    public function incorrectTransitionDefinitionDataProvider()
    {
        return array(
            'no definitions' => array(
                array()
            ),
            'unknown definition' => array(
                array('known' => array())
            )
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @dataProvider incorrectStepsDataProvider
     * @param array $steps
     */
    public function testUnknownStepException($steps)
    {
        $configuration = array(
            'test' => array(
                'transition_definition' => 'transition_definition',
                'label' => 'label',
                'step_to' => 'unknown'
            )
        );
        $definitions = array('transition_definition' => array());
        $this->assembler->assemble($configuration, $definitions, $steps);
    }

    public function incorrectStepsDataProvider()
    {
        return array(
            'no steps' => array(
                array()
            ),
            'unknown step' => array(
                array('known' => $this->getStep())
            )
        );
    }

    /**
     * @dataProvider configurationDataProvider
     * @param array $configuration
     * @param array $transitionDefinition
     */
    public function testAssemble(array $configuration, array $transitionDefinition)
    {
        $steps = array(
            'step' => $this->getStep()
        );

        $configuration = array_merge(
            $configuration,
            array(
                'is_start' => false,
                'form_type' => WorkflowStepType::NAME,
                'form_options' => array(),
                'frontend_options' => array(),
            )
        );

        $expectedCondition = null;
        $expectedPostAction = null;
        if (array_key_exists('conditions', $transitionDefinition)) {
            $expectedCondition = $this->getCondition();
            $this->conditionFactory->expects($this->once())
                ->method('create')
                ->with(
                    ConfigurableCondition::ALIAS,
                    $transitionDefinition['conditions']
                )
                ->will($this->returnValue($expectedCondition));
        }
        if (array_key_exists('post_actions', $transitionDefinition)) {
            $expectedPostAction = $this->getPostAction();
            $this->postActionFactory->expects($this->once())
                ->method('create')
                ->with(
                    ConfigurablePostAction::ALIAS,
                    $transitionDefinition['post_actions']
                )
                ->will($this->returnValue($this->getPostAction()));
        }

        $transitions = $this->assembler->assemble(
            array('test' => $configuration),
            $this->transitionDefinitions,
            $steps
        );
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $transitions);
        $this->assertCount(1, $transitions);
        $this->assertTrue($transitions->containsKey('test'));

        /** @var Transition $actualTransition */
        $actualTransition = $transitions->get('test');
        $this->assertEquals('test', $actualTransition->getName());
        $this->assertEquals($steps['step'], $actualTransition->getStepTo());
        $this->assertEquals($configuration['label'], $actualTransition->getLabel());
        $this->assertEquals($configuration['frontend_options'], $actualTransition->getFrontendOptions());
        $this->assertEquals($configuration['is_start'], $actualTransition->isStart());
        $this->assertEquals($configuration['form_type'], $actualTransition->getFormType());
        $this->assertEquals($configuration['form_options'], $actualTransition->getFormOptions());
        $this->assertEquals($expectedCondition, $actualTransition->getCondition());
        $this->assertEquals($expectedPostAction, $actualTransition->getPostAction());
    }

    public function configurationDataProvider()
    {
        return array(
            'empty_definition' => array(
                'configuration' => array(
                    'transition_definition' => 'empty_definition',
                    'label' => 'label',
                    'step_to' => 'step',
                    'form_type' => 'custom_workflow_transition',
                    'frontend_options' => array('class' => 'foo', 'icon' => 'bar'),
                ),
                'transitionDefinition' => $this->transitionDefinitions['empty_definition'],
            ),
            'with_condition' => array(
                'configuration' => array(
                    'transition_definition' => 'with_condition',
                    'label' => 'label',
                    'step_to' => 'step',
                ),
                'transitionDefinition' => $this->transitionDefinitions['with_condition'],
            ),
            'with_post_actions' => array(
                'configuration' => array(
                    'transition_definition' => 'with_post_actions',
                    'label' => 'label',
                    'step_to' => 'step',
                ),
                'transitionDefinition' => $this->transitionDefinitions['with_post_actions'],
            ),
            'full_definition' => array(
                'configuration' => array(
                    'transition_definition' => 'full_definition',
                    'label' => 'label',
                    'step_to' => 'step',
                ),
                'transitionDefinition' => $this->transitionDefinitions['full_definition'],
            ),
            'start_transition' => array(
                'configuration' => array(
                    'transition_definition' => 'empty_definition',
                    'label' => 'label',
                    'step_to' => 'step',
                    'is_start' => true,
                ),
                'transitionDefinition' => $this->transitionDefinitions['empty_definition'],
            ),
        );
    }

    protected function getStep()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Step')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getCondition()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface')
            ->getMockForAbstractClass();
    }

    protected function getPostAction()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface')
            ->getMockForAbstractClass();
    }
}
