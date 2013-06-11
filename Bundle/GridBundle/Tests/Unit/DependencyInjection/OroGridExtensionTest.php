<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\GridBundle\DependencyInjection\OroGridExtension;

class OroGridExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedParameters = array(
        OroGridExtension::PARAMETER_TRANSLATION_DOMAIN,
    );

    /**
     * @var array
     */
    protected $actualParameters = array();

    public function testLoad()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $container->expects($this->atLeastOnce())
            ->method('setDefinition');
        $container->expects($this->atLeastOnce())
            ->method('setParameter')
            ->will($this->returnCallback(array($this, 'setParameterCallback')));


        $gridExtension = new OroGridExtension();
        $configs = array();
        $gridExtension->load($configs, $container);

        foreach ($this->expectedParameters as $parameterName) {
            $this->assertArrayHasKey($parameterName, $this->actualParameters);
            $this->assertNotEmpty($this->actualParameters[$parameterName]);
        }
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
