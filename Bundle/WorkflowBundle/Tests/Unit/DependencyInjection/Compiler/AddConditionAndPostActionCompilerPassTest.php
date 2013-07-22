<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddConditionAndPostActionCompilerPass;

class AddConditionAndPostActionCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $conditionServices = array(
        'condition.definition.first'  => array(array('alias' => 'condition_first')),
        'condition.definition.second' => array(array()),
    );

    /**
     * @var array
     */
    protected $conditionTypes = array(
        'condition_first'             => 'condition.definition.first',
        'condition.definition.second' => 'condition.definition.second'
    );

    /**
     * @var array
     */
    protected $postActionServices = array(
        'post_action.definition.first' => array(array('alias' => 'post_action_first')),
        'post_action.definition.second' => array(array())
    );

    /**
     * @var array
     */
    protected $postActionTypes = array(
        'post_action_first'             => 'post_action.definition.first',
        'post_action.definition.second' => 'post_action.definition.second'
    );

    public function testProcess()
    {
        $definitionValueMap = array();
        $definitionBuilder = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->setMethods(array('setScope', 'replaceArgument'));

        // service definitions
        foreach (array_keys(array_merge($this->conditionServices, $this->postActionServices)) as $serviceId) {
            $definition = $definitionBuilder->getMock();
            $definition->expects($this->once())
                ->method('setScope')
                ->with(ContainerInterface::SCOPE_PROTOTYPE);

            $definitionValueMap[$serviceId] = $definition;
        }

        // factory definitions
        $factoryExpectations = array(
            AddConditionAndPostActionCompilerPass::CONDITION_FACTORY_SERVICE   => $this->conditionTypes,
            AddConditionAndPostActionCompilerPass::POST_ACTION_FACTORY_SERVICE => $this->postActionTypes,
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
            AddConditionAndPostActionCompilerPass::CONDITION_TAG   => $this->conditionServices,
            AddConditionAndPostActionCompilerPass::POST_ACTION_TAG => $this->postActionServices,
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
        $compilerPass = new AddConditionAndPostActionCompilerPass();
        $compilerPass->process($containerBuilder);
    }
}
