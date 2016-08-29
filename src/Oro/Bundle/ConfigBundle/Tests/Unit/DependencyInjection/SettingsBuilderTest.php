<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class SettingsBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testAppendBoolean()
    {
        $root = new ArrayNodeDefinition('root');

        $root
            ->children()
            ->scalarNode('foo')->end()
            ->end();

        SettingsBuilder::append(
            $root,
            [
                'greeting' => [
                    'value' => true,
                    'type'  => 'boolean',
                ],
            ]
        );

        $children = $this->getField($root, 'children');

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('settings', $children);
        $this->assertArrayHasKey('greeting', $this->getField($children['settings'], 'children'));
    }

    public function testAppendScalar()
    {
        $root = new ArrayNodeDefinition('root');

        $root
            ->children()
            ->scalarNode('foo')->end()
            ->end();

        SettingsBuilder::append(
            $root,
            [
                'level' => [
                    'value' => 10,
                ],
            ]
        );

        $children = $this->getField($root, 'children');

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('settings', $children);
        $this->assertArrayHasKey('level', $this->getField($children['settings'], 'children'));
    }

    public function testAppendArray()
    {
        $root = new ArrayNodeDefinition('root');

        $root
            ->children()
            ->scalarNode('foo')->end()
            ->end();

        SettingsBuilder::append(
            $root,
            [
                'list' => [
                    'value' => [1, 2, 3],
                    'type'  => 'array',
                ],
            ]
        );

        $children = $this->getField($root, 'children');
        $settings = $this->getField($children['settings'], 'children');
        $list = $this->getField($settings['list'], 'children');

        $this->assertCount(2, $children);
        $this->assertArrayHasKey('value', $list);
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition', $list['value']);
    }

    protected function getField($object, $field)
    {
        $reflection = new \ReflectionProperty($object, $field);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }
}
