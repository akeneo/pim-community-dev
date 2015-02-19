<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;

use Oro\Bundle\TranslationBundle\DependencyInjection\OroTranslationExtension;

class OroTranslationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedDefinitions = array(
        'oro_translation.form.type.translatable_entity',
        'oro_translation.controller',
    );

    /**
     * @var array
     */
    protected $expectedParameters = array(
        'oro_translation.form.type.translatable_entity.class',
        'translator.class',
        'oro_translation.controller.class',
        'oro_translation.js_translation.domains',
    );

    /**
     * @var array
     */
    protected $config = array(
        'oro_translation' => array(
            'js_translation' => array(
                'domains' => array('validators'),
                'debug' => false,
            )
        )
    );

    public function testLoad()
    {
        $actualDefinitions = array();
        $actualParameters  = array();

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('setDefinition', 'setParameter', 'getDefinition'))
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
        $container->expects($this->any())
            ->method('getDefinition')
            ->will(
                $this->returnCallback(
                    function ($name) use (&$actualDefinitions) {
                        return $actualDefinitions[$name];
                    }
                )
            );

        $extension = new OroTranslationExtension();
        $extension->load($this->config, $container);

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
