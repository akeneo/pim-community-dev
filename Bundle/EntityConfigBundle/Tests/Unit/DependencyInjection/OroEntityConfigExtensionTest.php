<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

use Oro\Bundle\EntityConfigBundle\DependencyInjection\OroEntityConfigExtension;

class OroEntityConfigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  OroEntityConfigExtension */
    private $extension;

    /**
     * @var array
     */
    private $actualParameters = array();

    protected function setUp()
    {
        $this->extension = new OroEntityConfigExtension();
    }

    public function testLoad()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
//        $container->expects($this->atLeastOnce())
//            ->method('setDefinition');
//        $container->expects($this->atLeastOnce())
//            ->method('setParameter')
//            ->will($this->returnCallback(array($this, 'setParameterCallback')));

        $configs = array();
//        $this->extension->load($configs, $container);

//        foreach ($this->expectedParameters as $parameterName) {
//            $this->assertArrayHasKey($parameterName, $this->actualParameters);
//            $this->assertNotEmpty($this->actualParameters[$parameterName]);
//        }
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
