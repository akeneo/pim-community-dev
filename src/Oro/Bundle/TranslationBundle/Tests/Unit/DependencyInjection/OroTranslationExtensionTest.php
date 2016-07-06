<?php

namespace Oro\Bundle\TranslationBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TranslationBundle\DependencyInjection\OroTranslationExtension;
use Symfony\Component\DependencyInjection\Definition;

class OroTranslationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedDefinitions = [
        'oro_translation.form.type.translatable_entity',
        'oro_translation.controller',
    ];

    /**
     * @var array
     */
    protected $expectedParameters = [
        'oro_translation.form.type.translatable_entity.class',
        'translator.class',
        'oro_translation.controller.class',
        'oro_translation.js_translation.domains',
    ];

    /**
     * @var array
     */
    protected $config = [
        'oro_translation' => [
            'js_translation' => [
                'domains' => ['validators'],
                'debug'   => false,
            ]
        ]
    ];

    public function testLoad()
    {
        $actualDefinitions = [];
        $actualParameters = [];

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['setDefinition', 'setParameter', 'getDefinition'])
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
