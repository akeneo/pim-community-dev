<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Model\WorkflowAssembler;
use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree;
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
        'start_step_name' => 'test_start_step',
        'managed_entity_class' => 'TestClass',
    );

    /**
     * @return Workflow
     */
    protected function createWorkflow()
    {
        $entityBinder = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\EntityBinder')
            ->disableOriginalConstructor()
            ->getMock();

        return new Workflow($entityBinder);
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
            ->setStartStep($this->workflowParameters['start_step_name'])
            ->setManagedEntityClass($this->workflowParameters['managed_entity_class'])
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
                ->with('oro_workflow.workflow_prototype')
                ->will($this->returnValue($workflow));
        }

        return $container;
    }

    /**
     * @param WorkflowDefinition $workflowDefinition
     * @return ConfigurationTree
     */
    protected function createConfigurationTreeMock(WorkflowDefinition $workflowDefinition)
    {
        $configurationTree = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree')
            ->disableOriginalConstructor()
            ->setMethods(array('parseConfiguration'))
            ->getMock();
        $configurationTree->expects($this->once())
            ->method('parseConfiguration')
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
            $expectedAttributeConfiguration = !empty($configuration[ConfigurationTree::NODE_ATTRIBUTES])
                ? $configuration[ConfigurationTree::NODE_ATTRIBUTES]
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
                ->with($configuration[ConfigurationTree::NODE_STEPS], $attributes)
                ->will($this->returnValue($steps));
        }

        return $stepAssembler;
    }

    /**
     * @param array $configuration
     * @param Collection $steps
     * @param Collection $transitions
     * @param boolean $expectations
     * @return TransitionAssembler
     */
    protected function createTransitionAssemblerMock(array $configuration, $steps, $transitions, $expectations = true)
    {
        $transitionAssembler = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\TransitionAssembler')
            ->disableOriginalConstructor()
            ->setMethods(array('assemble'))
            ->getMock();
        if ($expectations) {
            $transitionAssembler->expects($this->once())
                ->method('assemble')
                ->with(
                    $configuration[ConfigurationTree::NODE_TRANSITIONS],
                    $configuration[ConfigurationTree::NODE_TRANSITION_DEFINITIONS],
                    $steps
                )
                ->will($this->returnValue($transitions));
        }

        return $transitionAssembler;
    }

    /**
     * @param array $configuration
     * @dataProvider assembleDataProvider
     */
    public function testAssemble(array $configuration)
    {
        // source data
        $workflow = $this->createWorkflow();
        $workflowDefinition = $this->createWorkflowDefinition($configuration);
        $attributes = new ArrayCollection(array('attributes'));
        $steps = new ArrayCollection(array('steps'));
        $transitions = new ArrayCollection(array('transitions'));

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
        $actualWorkflow = $workflowAssembler->assemble($workflowDefinition);

        $this->assertEquals($workflow, $actualWorkflow);
        $this->assertEquals($workflowDefinition->getName(), $actualWorkflow->getName());
        $this->assertEquals($workflowDefinition->getLabel(), $actualWorkflow->getLabel());
        $this->assertEquals($workflowDefinition->isEnabled(), $actualWorkflow->isEnabled());
        $this->assertEquals($workflowDefinition->getManagedEntityClass(), $actualWorkflow->getManagedEntityClass());
        $this->assertEquals($attributes, $actualWorkflow->getAttributes());
        $this->assertEquals($steps, $actualWorkflow->getSteps());
        $this->assertEquals($transitions, $actualWorkflow->getTransitions());
    }

    /**
     * @return array
     */
    public function assembleDataProvider()
    {
        return array(
            'full configuration' => array(
                'configuration' => array(
                    ConfigurationTree::NODE_ATTRIBUTES => array('attributes_configuration'),
                    ConfigurationTree::NODE_STEPS => array('steps_configuration'),
                    ConfigurationTree::NODE_TRANSITIONS => array('transitions_configuration'),
                    ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array('definitions_configuration')
                ),
            ),
            'minimal configuration' => array(
                'configuration' => array(
                    ConfigurationTree::NODE_STEPS => array('steps_configuration'),
                    ConfigurationTree::NODE_TRANSITIONS => array('transitions_configuration'),
                    ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array('definitions_configuration')
                ),
            ),
        );
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
            ConfigurationTree::NODE_STEPS => array(),
            ConfigurationTree::NODE_TRANSITIONS => array('transitions_configuration'),
            ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array('definitions_configuration')
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
            ConfigurationTree::NODE_STEPS => array('steps_configuration'),
            ConfigurationTree::NODE_TRANSITIONS => array(),
            ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array('definitions_configuration')
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
            ConfigurationTree::NODE_STEPS => array('steps_configuration'),
            ConfigurationTree::NODE_TRANSITIONS => array('transitions_configuration'),
            ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array()
        );
        $this->assembleWorkflow($configuration);
    }
}
