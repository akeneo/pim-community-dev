<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Configuration;

use Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree;

class ConfigurationTreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $maximumConfiguration = array(
        ConfigurationTree::NODE_STEPS => array(
            'first_step' => array(
                'label'               => 'First Step',
                'template'            => 'My:Custom:first.html.twig',
                'order'               => 1,
                'is_final'            => false,
                'allowed_transitions' => array('first_transition', 'second_transition'),
                'form_type'           => 'custom_workflow_step',
                'form_options'        => array(
                    'key'              => 'value',
                    'attribute_fields' => array()
                ),
            ),
            'second_step' => array(
                'label'               => 'Second Step',
                'template'            => 'My:Custom:second.html.twig',
                'order'               => 2,
                'is_final'            => true,
                'allowed_transitions' => array(),
                'form_type'           => 'custom_workflow_step',
                'form_options'        => array(
                    'key'              => 'value',
                    'attribute_fields' => array()
                ),
            )
        ),
        ConfigurationTree::NODE_ATTRIBUTES => array(
            'first_attribute' => array(
                'label'   => 'First Attribute',
                'type'    => 'object',
                'options' => array('class' => 'DateTime'),
            ),
            'second_attribute' => array(
                'type'  => 'entity',
                'label' => 'Second Attribute',
                'options' => array('class' => 'SomeEntity', 'managed_entity' => true),
            )
        ),
        ConfigurationTree::NODE_TRANSITIONS => array(
            'first_transition' => array(
                'label'                 => 'First Transition',
                'step_to'               => 'second_step',
                'transition_definition' => 'first_transition_definition'
            ),
            'second_transition' => array(
                'label'                 => 'Second Transition',
                'step_to'               => 'second_step',
                'transition_definition' => 'second_transition_definition'
            )
        ),
        ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array(
            'first_transition_definition' => array(
                'conditions' => array(
                    '@true' => null,
                ),
                'post_actions' => array(
                    '@assign_value' => array('parameters' => array('$attribute', 'first_value'))
                ),
            ),
            'second_transition_definition' => array(
                'conditions' => array(
                    '@false' => null,
                ),
                'post_actions' => array(
                    '@assign_value' => array('parameters' => array('$attribute', 'second_value'))
                ),
            ),
        )
    );

    /**
     * @param array $sourceConfiguration
     * @param array $expectedConfiguration
     * @dataProvider parseConfigurationDataProvider
     */
    public function testParseConfiguration(array $sourceConfiguration, array $expectedConfiguration)
    {
        $configurationTree = new ConfigurationTree();
        $actualConfiguration = $configurationTree->parseConfiguration($sourceConfiguration);
        $this->assertEquals($expectedConfiguration, $actualConfiguration);
    }

    public function parseConfigurationDataProvider()
    {
        return array(
            'maximum configuration' => array(
                'sourceConfiguration'   => $this->maximumConfiguration,
                'expectedConfiguration' => $this->maximumConfiguration,
            ),
            'minimum configuration' => array(
                'sourceConfiguration' => array(
                    ConfigurationTree::NODE_STEPS => array(
                        'first_step' => array(
                            'label' => 'First Step',
                        ),
                    ),
                    ConfigurationTree::NODE_TRANSITIONS => array(
                        'first_transition' => array(
                            'label'                 => 'First Transition',
                            'step_to'               => 'second_step',
                            'transition_definition' => 'first_transition_definition'
                        ),
                    ),
                    ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array(
                        'first_transition_definition' => array()
                    ),
                ),
                'expectedConfiguration' => array(
                    ConfigurationTree::NODE_STEPS => array(
                        'first_step' => array(
                            'label'               => 'First Step',
                            'template'            => null,
                            'order'               => 0,
                            'is_final'            => false,
                            'allowed_transitions' => array(),
                            'form_type'           => 'oro_workflow_step',
                        ),
                    ),
                    ConfigurationTree::NODE_ATTRIBUTES => array(),
                    ConfigurationTree::NODE_TRANSITIONS => array(
                        'first_transition' => array(
                            'label'                 => 'First Transition',
                            'step_to'               => 'second_step',
                            'transition_definition' => 'first_transition_definition'
                        ),
                    ),
                    ConfigurationTree::NODE_TRANSITION_DEFINITIONS => array(
                        'first_transition_definition' => array()
                    ),
                ),
            )
        );
    }

    //@codingStandardsIgnoreStart
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid configuration for path "configuration.attributes.first_attribute.type": Invalid type "text", allowed types are "bool", "boolean", "int", "integer", "float", "string", "array", "object", "entity"
     */
    //@codingStandardsIgnoreEnd
    public function testUnexpectedAttributeType()
    {
        $configuration = $this->maximumConfiguration;
        $configuration[ConfigurationTree::NODE_ATTRIBUTES] = array(
            'first_attribute' => array(
                'label'   => 'First Attribute',
                'type'    => 'text',
                'options' => array('class' => 'DateTime'),
            ),
        );

        $configurationTree = new ConfigurationTree();
        $configurationTree->parseConfiguration($configuration);
    }

    /**
     * @dataProvider nodesWithRequiredElementsDataProvider
     *
     * @param string $nodeName
     */
    public function testNodesWithRequiredElementsAreEmpty($nodeName)
    {
        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            sprintf('The path "configuration.%s" should have at least 1 element(s) defined.', $nodeName)
        );
        $configuration = $this->maximumConfiguration;
        $configuration[$nodeName] = array();

        $configurationTree = new ConfigurationTree();
        $configurationTree->parseConfiguration($configuration);
    }

    public function nodesWithRequiredElementsDataProvider()
    {
        return array(
            array(ConfigurationTree::NODE_STEPS),
            array(ConfigurationTree::NODE_TRANSITION_DEFINITIONS),
            array(ConfigurationTree::NODE_TRANSITIONS),
        );
    }

    /**
     * @dataProvider attributeOptionClassTypesDataProvider
     *
     * @param string $type
     */
    public function testAttributeOptionClassIsMissing($type)
    {
        $this->setExpectedException(
            'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
            sprintf(
                'Invalid configuration for path "configuration.attributes.first_attribute": '
                . 'Option "class" is required for type "%s"',
                $type
            )
        );
        $configuration = $this->maximumConfiguration;
        $configuration[ConfigurationTree::NODE_ATTRIBUTES] = array(
            'first_attribute' => array(
                'label'   => 'First Attribute',
                'type'    => $type,
            ),
        );

        $configurationTree = new ConfigurationTree();
        $configurationTree->parseConfiguration($configuration);
    }

    public function attributeOptionClassTypesDataProvider()
    {
        return array(
            array('object'),
            array('entity'),
        );
    }
}
