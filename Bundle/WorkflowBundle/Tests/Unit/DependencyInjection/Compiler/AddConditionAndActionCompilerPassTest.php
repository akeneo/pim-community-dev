<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddConditionAndActionCompilerPass;

class AddConditionAndActionCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $conditionServices = array(
        'condition.definition.first'  => array(array('alias' => 'condition_first|condition_first_alias')),
        'condition.definition.second' => array(array()),
    );

    /**
     * @var array
     */
    protected $conditionTypes = array(
        'condition_first'             => 'condition.definition.first',
        'condition_first_alias'       => 'condition.definition.first',
        'condition.definition.second' => 'condition.definition.second'
    );

    /**
     * @var array
     */
    protected $actionServices = array(
        'action.definition.first' => array(array('alias' => 'action_first|action_first_alias')),
        'action.definition.second' => array(array())
    );

    /**
     * @var array
     */
    protected $actionTypes = array(
        'action_first'             => 'action.definition.first',
        'action_first_alias'       => 'action.definition.first',
        'action.definition.second' => 'action.definition.second'
    );

    public function testProcess()
    {
        $definitionValueMap = array();
        $definitionBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->setMethods(array('setScope', 'replaceArgument'));

        // service definitions
        foreach (array_keys(array_merge($this->conditionServices, $this->actionServices)) as $serviceId) {
            $definition = $definitionBuilder->getMock();
            $definition->expects($this->once())
                ->method('setScope')
                ->with(ContainerInterface::SCOPE_PROTOTYPE);

            $definitionValueMap[$serviceId] = $definition;
        }

        // factory definitions
        $factoryExpectations = array(
            AddConditionAndActionCompilerPass::CONDITION_FACTORY_SERVICE   => $this->conditionTypes,
            AddConditionAndActionCompilerPass::ACTION_FACTORY_SERVICE => $this->actionTypes,
        );
        foreach ($factoryExpectations as $factoryServiceId => $factoryTypes) {
            $factoryDefinition = $definitionBuilder->getMock();
            $factoryDefinition->expects($this->once())
                ->method('replaceArgument')
                ->with(1, $factoryTypes);

            $definitionValueMap[$factoryServiceId] = $factoryDefinition;
        }

        // container builder
        $containerBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getDefinition', 'findTaggedServiceIds'))
            ->getMock();
        $containerBuilder->expects($this->any())
            ->method('getDefinition')
            ->will(
                $this->returnCallback(
                    function ($serviceId) use ($definitionValueMap) {
                        return isset($definitionValueMap[$serviceId]) ? $definitionValueMap[$serviceId] : null;
                    }
                )
            );
        $tagMap = array(
            AddConditionAndActionCompilerPass::CONDITION_TAG   => $this->conditionServices,
            AddConditionAndActionCompilerPass::ACTION_TAG => $this->actionServices,
        );
        $containerBuilder->expects($this->any())
            ->method('findTaggedServiceIds')
            ->will(
                $this->returnCallback(
                    function ($tagName) use ($tagMap) {
                        return isset($tagMap[$tagName]) ? $tagMap[$tagName] : null;
                    }
                )
            );

        // test
        $compilerPass = new AddConditionAndActionCompilerPass();
        $compilerPass->process($containerBuilder);
    }
}
