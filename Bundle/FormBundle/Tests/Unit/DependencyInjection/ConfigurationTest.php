<?php
namespace Oro\Bundle\FormBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Processor;

use Oro\Bundle\FormBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $builder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $builder);

        /** @var $root ArrayNode */
        $root = $builder->buildTree();
        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $root);
        $this->assertEquals('oro_form', $root->getName());
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($configs, $processedConfig)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $this->assertEquals($processedConfig, $processor->processConfiguration($configuration, $configs));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function processDataProvider()
    {
        return array(
            'empty config' => array(
                array(array()),
                array(
                    'autocomplete_entities' => array()
                ),
            ),
            'single property' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo_type',
                                'property' => 'bar',
                                'properties' => array(),
                                'entity_class' => 'FooClassName'
                            )
                        )
                    )
                ),
                array(
                    'autocomplete_entities' => array(
                        'foo_name' => array(
                            'type' => 'foo_type',
                            'properties' => array(
                                array('name' => 'bar')
                            ),
                            'options' => array(),
                            'form_options' => array(),
                            'entity_class' => 'FooClassName',
                            'route' => 'oro_form_autocomplete_search',
                            'view' => 'OroFormBundle:EntityAutocomplete:search.json.twig'
                        )
                    )
                )
            ),
            'short properties' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo_type',
                                'property' => null,
                                'properties' => array(
                                    'bar' => null,
                                    'baz' => null
                                ),
                                'entity_class' => 'FooClassName'
                            )
                        )
                    )
                ),
                array(
                    'autocomplete_entities' => array(
                        'foo_name' => array(
                            'type' => 'foo_type',
                            'properties' => array(
                                array('name' => 'bar'),
                                array('name' => 'baz'),
                            ),
                            'options' => array(),
                            'form_options' => array(),
                            'entity_class' => 'FooClassName',
                            'route' => 'oro_form_autocomplete_search',
                            'view' => 'OroFormBundle:EntityAutocomplete:search.json.twig'
                        )
                    )
                )
            ),
            'full properties' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo_type',
                                'properties' => array(
                                    array('name' => 'bar', 'bar' => 'baz'),
                                    array('name' => 'baz', 'baz' => 'bar')
                                ),
                                'entity_class' => 'FooClassName'
                            )
                        )
                    )
                ),
                array(
                    'autocomplete_entities' => array(
                        'foo_name' => array(
                            'type' => 'foo_type',
                            'properties' => array(
                                array('name' => 'bar', 'bar' => 'baz'),
                                array('name' => 'baz', 'baz' => 'bar')
                            ),
                            'options' => array(),
                            'form_options' => array(),
                            'entity_class' => 'FooClassName',
                            'route' => 'oro_form_autocomplete_search',
                            'view' => 'OroFormBundle:EntityAutocomplete:search.json.twig'
                        )
                    )
                )
            ),
            'custom_route' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo_type',
                                'property' => 'bar',
                                'entity_class' => 'FooClassName',
                                'route' => 'custom_route',
                            )
                        )
                    )
                ),
                array(
                    'autocomplete_entities' => array(
                        'foo_name' => array(
                            'type' => 'foo_type',
                            'properties' => array(
                                array('name' => 'bar')
                            ),
                            'options' => array(),
                            'form_options' => array(),
                            'entity_class' => 'FooClassName',
                            'route' => 'custom_route',
                            'view' => 'OroFormBundle:EntityAutocomplete:search.json.twig'
                        )
                    )
                )
            ),
            'custom_view' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo_type',
                                'property' => 'bar',
                                'entity_class' => 'FooClassName',
                                'view' => 'custom_view',
                            )
                        )
                    )
                ),
                array(
                    'autocomplete_entities' => array(
                        'foo_name' => array(
                            'type' => 'foo_type',
                            'properties' => array(
                                array('name' => 'bar')
                            ),
                            'options' => array(),
                            'form_options' => array(),
                            'entity_class' => 'FooClassName',
                            'route' => 'oro_form_autocomplete_search',
                            'view' => 'custom_view'
                        )
                    )
                )
            )
        );
    }

    /**
     * @dataProvider processFailsDataProvider
     */
    public function testProcessFails($configs, $expectedException, $expectedExceptionMessage)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $processor->processConfiguration($configuration, $configs);
    }

    public function processFailsDataProvider()
    {
        return array(
            'type required' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array()
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'The child node "type" at path "oro_form.autocomplete_entities.foo_name" must be configured.'
            ),
            'entity_class required' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo',
                                'property' => 'bar'
                            )
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'The child node "entity_class" at path "oro_form.autocomplete_entities.foo_name" must be configured.'
            ),
            'properties required' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo'
                            )
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'The child node "properties" at path "oro_form.autocomplete_entities.foo_name" must be configured.'
            ),
            'properties at least one element' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo',
                                'property' => null,
                                'properties' => array()
                            )
                        )
                    )
                ),
                'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
                'The path "oro_form.autocomplete_entities.foo_name.properties" should have at least 1 element(s) defined.'
            ),
            'property cannot be used with properties' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo',
                                'property' => 'bar',
                                'properties' => array('baz' => null)
                            )
                        )
                    )
                ),
                'Exception',
                'Option "property" cannot be set with option "properties".'
            ),
            'property name is required' => array(
                array(
                    array(
                        'autocomplete_entities' => array(
                            'foo_name' => array(
                                'type' => 'foo',
                                'properties' => array(array())
                            )
                        )
                    )
                ),
                'Exception',
                'name is required option.'
            ),
        );
    }
}
