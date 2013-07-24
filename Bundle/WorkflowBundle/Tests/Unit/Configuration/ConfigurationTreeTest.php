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
                'attributes'          => array('first_attribute'),
            ),
            'second_step' => array(
                'label'               => 'Second Step',
                'template'            => 'My:Custom:second.html.twig',
                'order'               => 2,
                'is_final'            => true,
                'allowed_transitions' => array(),
                'attributes'          => array('second_attribute'),
            )
        ),
        ConfigurationTree::NODE_ATTRIBUTES => array(
            'first_attribute' => array(
                'form_type' => 'text',
                'label'     => 'First Attribute',
                'options'   => array('required' => true),
            ),
            'second_attribute' => array(
                'form_type' => 'text',
                'label'     => 'Second Attribute',
                'options'   => array('required' => true),
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
                            'attributes'          => array(),
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
}
