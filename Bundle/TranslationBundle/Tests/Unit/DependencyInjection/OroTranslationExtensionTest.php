<?php

namespace Oro\Bundle\TranslationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

use Oro\Bundle\TranslationBundle\DependencyInjection\OroTranslationExtension;

class OroTranslationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedDefinitions = array(
        'oro_translation.form.type.translatable_entity',
        'oro_translation.form.type.jqueryselect2_translatable_entity'
    );

    /**
     * @var array
     */
    protected $expectedParameters = array(
        'oro_translation.form.type.translatable_entity.class',
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

        $extension = new OroTranslationExtension();
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
