<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowAssembler;
use Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration;
use Oro\Bundle\WorkflowBundle\Model\AttributeAssembler;
use Oro\Bundle\WorkflowBundle\Model\StepAssembler;
use Oro\Bundle\WorkflowBundle\Model\TransitionAssembler;

class WorkflowAssemblerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $workflowParameters = array(
        'name' => 'test_name',
        'label' => 'Test Label',
        'enabled' => true,
        'type' => Workflow::TYPE_ENTITY,
    );

    protected $stepConfiguration = array(
        'label' => 'Test'
    );

    protected $transitionConfiguration = array(
        'label' => 'Test',
        'step_to' => 'test_step',
        'transition_definition' => 'test_transition_definition'
    );

    protected $transitionDefinition = array(
        'test_transition_definition' => array()
    );

    /**
     * @return Workflow
     */
    protected function createWorkflow()
    {
        return new Workflow();
    }

    /**
     * @param array $configuration
     * @return WorkflowDefinition
     */
    protected function createWorkflowDefinition(array $configuration)
    {
        $workflowDefinition = new WorkflowDefinition();
        $workflowDefinition
            ->setName($this->workflowParameters['name'])
            ->setLabel($this->workflowParameters['label'])
            ->setEnabled($this->workflowParameters['enabled'])
            ->setType($this->workflowParameters['type'])
            ->setConfiguration($configuration);

        return $workflowDefinition;
    }

    /**
     * @param Workflow $workflow
     * @param boolean $expectations
     * @return ContainerInterface
     */
    protected function createContainerMock(Workflow $workflow, $expectations = true)
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMockForAbstractClass();
        if ($expectations) {
            $container->expects($this->once())
                ->method('get')
                ->with('oro_workflow.prototype.workflow')
                ->will($this->returnValue($workflow));
        }

        return $container;
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return WorkflowConfiguration
     */
    protected function createConfigurationTreeMock(WorkflowDefinition $workflowDefinition)
    {
        $configurationTree = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration')
            ->disableOriginalConstructor()
            ->setMethods(array('processConfiguration'))
            ->getMock();
        $configurationTree->expects($this->once())
            ->method('processConfiguration')
            ->with($workflowDefinition->getConfiguration())
            ->will($this->returnValue($workflowDefinition->getConfiguration()));

        return $configurationTree;
    }

    /**
     * @param array $configuration
     * @param Collection $attributes
     * @param boolean $expectations
     * @return AttributeAssembler
     */
    protected function createAttributeAssemblerMock(array $configuration, $attributes, $expectations = true)
    {
        $attributeAssembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\AttributeAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        if ($expectations) {
            $expectedAttributeConfiguration = !empty($configuration[WorkflowConfiguration::NODE_ATTRIBUTES])
                ? $configuration[WorkflowConfiguration::NODE_ATTRIBUTES]
                : array();
            $attributeAssembler->expects($this->once())
                ->method('assemble')
                ->with($expectedAttributeConfiguration)
                ->will($this->returnValue($attributes));
        }

        return $attributeAssembler;
    }

    /**
     * @param array $configuration
     * @param Collection $attributes
     * @param Collection $steps
     * @param boolean $expectations
     * @return StepAssembler
     */
    protected function createStepAssemblerMock(array $configuration, $attributes, $steps, $expectations = true)
    {
        $stepAssembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\StepAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        if ($expectations) {
            $stepAssembler->expects($this->once())
                ->method('assemble')
                ->with($configuration[WorkflowConfiguration::NODE_STEPS], $attributes)
                ->will($this->returnValue($steps));
        }

        return $stepAssembler;
    }

    protected function getStepMock()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Step')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getTransitionMock($isStart)
    {
        $transition = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Transition')
            ->disableOriginalConstructor()
            ->getMock();
        $transition->expects($this->any())
            ->method('isStart')
            ->will($this->returnValue($isStart));
        return $transition;
    }

    protected function getAttributeMock()
    {
        return $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $configuration
     * @param Collection $steps
     * @param Collection $transitions
     * @param boolean $expectations
     * @param null|array $expectedTransitions
     * @param null|array $expectedDefinitions
     * @internal param null|string $startStepName
     * @return TransitionAssembler
     */
    protected function createTransitionAssemblerMock(
        array $configuration,
        $steps,
        $transitions,
        $expectations = true,
        $expectedTransitions = null,
        $expectedDefinitions = null
    ) {
        $transitionAssembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\TransitionAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        if ($expectations) {
            $expectedTransitions = $expectedTransitions ?
                $expectedTransitions :
                $configuration[WorkflowConfiguration::NODE_TRANSITIONS];
            $expectedDefinitions = $expectedDefinitions ?
                $expectedDefinitions :
                $configuration[WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS];
            $transitionAssembler->expects($this->once())
                ->method('assemble')
                ->with($expectedTransitions, $expectedDefinitions, $steps)
                ->will($this->returnValue($transitions));
        }

        return $transitionAssembler;
    }

    /**
     * @param array $configuration
     * @param string $startStepName
     * @param array $expectedTransitions
     * @param array $expectedDefinitions
     * @dataProvider assembleDataProvider
     */
    public function testAssemble(array $configuration, $startStepName, $expectedTransitions, $expectedDefinitions)
    {
        // source data
        $workflow = $this->createWorkflow();
        $workflowDefinition = $this->createWorkflowDefinition($configuration);
        $attributes = new ArrayCollection(array('test' => $this->getAttributeMock()));
        $steps = new ArrayCollection(array('test_start_step' => $this->getStepMock()));

        $transitions = array('test_transition' => $this->getTransitionMock(false));
        if (!$startStepName) {
            $transitions['test_start_transition'] = $this->getTransitionMock(true);
        } else {
            $transitions['__start__'] = $this->getTransitionMock(true);
            $workflowDefinition->setStartStep($startStepName);
        }
        $transitions = new ArrayCollection($transitions);

        // mocks
        $container = $this->createContainerMock($workflow);
        $configurationTree = $this->createConfigurationTreeMock($workflowDefinition);
        $attributeAssembler = $this->createAttributeAssemblerMock($configuration, $attributes);
        $stepAssembler = $this->createStepAssemblerMock($configuration, $attributes, $steps);
        $transitionAssembler = $this->createTransitionAssemblerMock(
            $configuration,
            $steps,
            $transitions,
            true,
            $expectedTransitions,
            $expectedDefinitions
        );

        // test
        $workflowAssembler = new WorkflowAssembler(
            $container,
            $configurationTree,
            $attributeAssembler,
            $stepAssembler,
            $transitionAssembler
        );
        $actualWorkflow = $workflowAssembler->assemble($workflowDefinition);

        $this->assertEquals($workflow, $actualWorkflow);
        $this->assertEquals($workflowDefinition->getName(), $actualWorkflow->getName());
        $this->assertEquals($workflowDefinition->getLabel(), $actualWorkflow->getLabel());
        $this->assertEquals($workflowDefinition->isEnabled(), $actualWorkflow->isEnabled());
        $this->assertEquals($attributes, $actualWorkflow->getAttributeManager()->getAttributes());
        $this->assertEquals($steps, $actualWorkflow->getStepManager()->getSteps());
        $this->assertEquals($transitions, $actualWorkflow->getTransitionManager()->getTransitions());
    }

    /**
     * @return array
     */
    public function assembleDataProvider()
    {
        $transitions = array('test_transition' => $this->transitionConfiguration);
        $fullConfig = array(
            WorkflowConfiguration::NODE_ATTRIBUTES => array('attributes_configuration'),
            WorkflowConfiguration::NODE_STEPS => array('test_step' => $this->stepConfiguration),
            WorkflowConfiguration::NODE_TRANSITIONS => $transitions,
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => $this->transitionDefinition
        );
        $minimalConfig = array(
            WorkflowConfiguration::NODE_STEPS => array('test_step' => $this->stepConfiguration),
            WorkflowConfiguration::NODE_TRANSITIONS => $transitions,
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => $this->transitionDefinition
        );
        $customStartTransition = array(
            Workflow::DEFAULT_START_TRANSITION_NAME => array(
                'label' => 'My Label',
                'step_to' => 'custom_step',
                'is_start' => true,
                'transition_definition' => '__start___definition'
            )
        );
        $customStartDefinition = array('__start___definition' => array('conditions' => array()));
        $fullConfigWithCustomStart = $minimalConfig;
        $fullConfigWithCustomStart[WorkflowConfiguration::NODE_TRANSITIONS] += $customStartTransition;
        $fullConfigWithCustomStart[WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS] += $customStartDefinition;

        $label = $this->workflowParameters['label'];
        $getDefaultTransition = function ($stepName) use ($label) {
            return array(
                Workflow::DEFAULT_START_TRANSITION_NAME => array(
                    'label' => $label,
                    'step_to' => $stepName,
                    'is_start' => true,
                    'transition_definition' => '__start___definition'
                )
            );
        };

        return array(
            'full configuration with start' => array(
                'configuration' => $fullConfig,
                'startStepName' => 'test_start_step',
                'expectedTransitions' => $transitions + $getDefaultTransition('test_start_step'),
                'expectedDefinitions' => $this->transitionDefinition + array('__start___definition' => array())
            ),
            'minimal configuration with start' => array(
                'configuration' => $minimalConfig,
                'startStepName' => 'test_start_step',
                'expectedTransitions' => $transitions + $getDefaultTransition('test_start_step'),
                'expectedDefinitions' => $this->transitionDefinition + array('__start___definition' => array())
            ),
            'full configuration without start' => array(
                'configuration' => $fullConfig,
                'startStepName' => null,
                'expectedTransitions' => $transitions,
                'expectedDefinitions' => array()
            ),
            'minimal configuration without start' => array(
                'configuration' => $minimalConfig,
                'startStepName' => null,
                'expectedTransitions' => $transitions,
                'expectedDefinitions' => array()
            ),
            'full configuration with start custom config' => array(
                'configuration' => $fullConfigWithCustomStart,
                'startStepName' => 'test_start_step',
                'expectedTransitions' => $transitions + $customStartTransition,
                'expectedDefinitions' => $this->transitionDefinition + $customStartDefinition
            ),
        );
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @expectedExceptionMessage Workflow "test_name" does not contains neither start step nor start transitions
     */
    public function testAssembleStartTransitionException()
    {
        $configuration = array(
            WorkflowConfiguration::NODE_ATTRIBUTES => array('attributes_configuration'),
            WorkflowConfiguration::NODE_STEPS => array('test_step' => $this->stepConfiguration),
            WorkflowConfiguration::NODE_TRANSITIONS => $this->transitionConfiguration,
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => $this->transitionDefinition
        );

        // source data
        $workflow = $this->createWorkflow();
        $workflowDefinition = $this->createWorkflowDefinition($configuration);
        $attributes = new ArrayCollection(array('test' => $this->getAttributeMock()));
        $steps = new ArrayCollection(array('test_start_step' => $this->getStepMock()));

        $transitions = array('test_transition' => $this->getTransitionMock(false));

        // mocks
        $container = $this->createContainerMock($workflow);
        $configurationTree = $this->createConfigurationTreeMock($workflowDefinition);
        $attributeAssembler = $this->createAttributeAssemblerMock($configuration, $attributes);
        $stepAssembler = $this->createStepAssemblerMock($configuration, $attributes, $steps);
        $transitionAssembler = $this->createTransitionAssemblerMock($configuration, $steps, $transitions);

        // test
        $workflowAssembler = new WorkflowAssembler(
            $container,
            $configurationTree,
            $attributeAssembler,
            $stepAssembler,
            $transitionAssembler
        );
        $workflowAssembler->assemble($workflowDefinition);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @expectedExceptionMessage Workflow "test_name" has type "entity" and cannot support form options in step "test_step"
     */
    // @codingStandardsIgnoreEnd
    public function testAssembleEntityWorkflowStepFormOptionsException()
    {
        $configuration = array(
            'type' => 'entity',
            WorkflowConfiguration::NODE_ATTRIBUTES => array('attributes_configuration'),
            WorkflowConfiguration::NODE_STEPS => array('test_step' => $this->stepConfiguration),
            WorkflowConfiguration::NODE_TRANSITIONS => $this->transitionConfiguration,
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => $this->transitionDefinition
        );

        $stepMock = $this->getStepMock();
        $stepMock->expects($this->once())->method('getName')->will($this->returnValue('test_step'));
        $stepMock->expects($this->once())->method('getFormOptions')->will($this->returnValue(array('foo' => 'bar')));

        // source data
        $workflow = $this->createWorkflow();
        $workflowDefinition = $this->createWorkflowDefinition($configuration);
        $workflowDefinition->setType(Workflow::TYPE_ENTITY);
        $attributes = new ArrayCollection(array('test' => $this->getAttributeMock()));
        $steps = new ArrayCollection(array('test_start_step' => $stepMock));

        $transitions = array('test_transition' => $this->getTransitionMock(true));

        // mocks
        $container = $this->createContainerMock($workflow);
        $configurationTree = $this->createConfigurationTreeMock($workflowDefinition);
        $attributeAssembler = $this->createAttributeAssemblerMock($configuration, $attributes);
        $stepAssembler = $this->createStepAssemblerMock($configuration, $attributes, $steps);
        $transitionAssembler = $this->createTransitionAssemblerMock($configuration, $steps, $transitions);

        // test
        $workflowAssembler = new WorkflowAssembler(
            $container,
            $configurationTree,
            $attributeAssembler,
            $stepAssembler,
            $transitionAssembler
        );
        $workflowAssembler->assemble($workflowDefinition);
    }

    /**
     * @param array $configuration
     */
    protected function assembleWorkflow(array $configuration)
    {
        $workflow = $this->createWorkflow();
        $workflowDefinition = $this->createWorkflowDefinition($configuration);
        $attributes = new ArrayCollection();
        $steps = new ArrayCollection();
        $transitions = new ArrayCollection();

        $container = $this->createContainerMock($workflow, false);
        $configurationTree = $this->createConfigurationTreeMock($workflowDefinition);
        $attributeAssembler = $this->createAttributeAssemblerMock($configuration, $attributes, false);
        $stepAssembler = $this->createStepAssemblerMock($configuration, $attributes, $steps, false);
        $transitionAssembler = $this->createTransitionAssemblerMock($configuration, $steps, $transitions, false);

        $workflowAssembler = new WorkflowAssembler(
            $container,
            $configurationTree,
            $attributeAssembler,
            $stepAssembler,
            $transitionAssembler
        );
        $workflowAssembler->assemble($workflowDefinition);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @expectedExceptionMessage Option "steps" is required
     */
    public function testAssembleNoStepsConfigurationException()
    {
        $configuration = array(
            WorkflowConfiguration::NODE_STEPS => array(),
            WorkflowConfiguration::NODE_TRANSITIONS => array('test_transition' => $this->transitionConfiguration),
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => array($this->transitionDefinition)
        );
        $this->assembleWorkflow($configuration);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @expectedExceptionMessage Option "transitions" is required
     */
    public function testAssembleNoTransitionsConfigurationException()
    {
        $configuration = array(
            WorkflowConfiguration::NODE_STEPS => array('step_one' => $this->stepConfiguration),
            WorkflowConfiguration::NODE_TRANSITIONS => array(),
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => array($this->transitionDefinition)
        );
        $this->assembleWorkflow($configuration);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\AssemblerException
     * @expectedExceptionMessage Option "transition_definitions" is required
     */
    public function testAssembleNoTransitionDefinitionsConfigurationException()
    {
        $configuration = array(
            WorkflowConfiguration::NODE_STEPS => array('test_step' => $this->stepConfiguration),
            WorkflowConfiguration::NODE_TRANSITIONS => array('test_transition' => $this->transitionConfiguration),
            WorkflowConfiguration::NODE_TRANSITION_DEFINITIONS => array()
        );
        $this->assembleWorkflow($configuration);
    }
}
