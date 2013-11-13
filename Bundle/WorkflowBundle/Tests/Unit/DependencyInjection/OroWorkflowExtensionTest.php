<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

use Oro\Bundle\WorkflowBundle\DependencyInjection\OroWorkflowExtension;

class OroWorkflowExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedDefinitions = array(
        'oro_workflow.configuration_pass.replace_property_path',
        'oro_workflow.condition_factory',
        'oro_workflow.action_factory',
        'oro_workflow.configuration.config_provider',
        'oro_workflow.form.type.step',
    );

    /**
     * @var array
     */
    protected $expectedParameters = array(
        'oro_workflow.configuration_pass.replace_property_path.class',
        'oro_workflow.condition_factory.class',
        'oro_workflow.action_factory.class',
        'oro_workflow.configuration.config_provider.class',
        'oro_workflow.form.type.step.class',
    );

    public function testLoad()
    {
        $actualDefinitions = array();
        $actualParameters  = array();

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('setDefinition', 'setParameter'))
            ->getMock();
        $container->expects($this->any())
            ->method('setDefinition')
            ->will(
                $this->returnCallback(
                    function ($id, Definition $definition) use (&$actualDefinitions) {
                        $actualDefinitions[$id] = $definition;
                    }
                )
            );
        $container->expects($this->any())
            ->method('setParameter')
            ->will(
                $this->returnCallback(
                    function ($name, $value) use (&$actualParameters) {
                        $actualParameters[$name] = $value;
                    }
                )
            );

        $extension = new OroWorkflowExtension();
        $extension->load(array(), $container);

        foreach ($this->expectedDefinitions as $serviceId) {
            $this->assertArrayHasKey($serviceId, $actualDefinitions);
            $this->assertNotEmpty($actualDefinitions[$serviceId]);
        }

        foreach ($this->expectedParameters as $parameterName) {
            $this->assertArrayHasKey($parameterName, $actualParameters);
            $this->assertNotEmpty($actualParameters[$parameterName]);
        }
    }
}
