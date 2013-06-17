<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Oro\Bundle\FilterBundle\DependencyInjection\OroFilterExtension;

class OroFilterExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedDefinitions = array(
        'oro_filter.form.type.date_range',
        'oro_filter.form.type.filter.datetime_range',
        'oro_filter.twig.extension.render_layout',
        'oro_filter.twig.extension.render_header'
    );

    /**
     * @var array
     */
    protected $expectedParameters = array(
        OroFilterExtension::PARAMETER_TWIG_LAYOUT,
        OroFilterExtension::PARAMETER_TWIG_HEADER,
    );

    /**
     * @var array
     */
    protected $actualDefinitions = array();

    /**
     * @var array
     */
    protected $actualParameters = array();

    public function testLoad()
    {
        $container = $this->getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('setDefinition', 'setParameter')
        );
        $container->expects($this->any())
            ->method('setDefinition')
            ->will($this->returnCallback(array($this, 'setDefinitionCallback')));
        $container->expects($this->any())
            ->method('setParameter')
            ->will($this->returnCallback(array($this, 'setParameterCallback')));

        $extension = new OroFilterExtension();
        $extension->load(array(), $container);

        foreach ($this->expectedDefinitions as $serviceId) {
            $this->assertArrayHasKey($serviceId, $this->actualDefinitions);
            $this->assertNotEmpty($this->actualDefinitions[$serviceId]);
        }

        foreach ($this->expectedParameters as $parameterName) {
            $this->assertArrayHasKey($parameterName, $this->actualParameters);
            $this->assertNotEmpty($this->actualParameters[$parameterName]);
        }
    }

    /**
     * Callback for ContainerBuilder::setDefinition
     *
     * @param string $id
     * @param Definition $definition
     */
    public function setDefinitionCallback($id, Definition $definition)
    {
        $this->actualDefinitions[$id] = $definition;
    }

    /**
     * Callback for ContainerBuilder::setParameter
     *
     * @param string $name
     * @param string $value
     */
    public function setParameterCallback($name, $value)
    {
        $this->actualParameters[$name] = $value;
    }
}
