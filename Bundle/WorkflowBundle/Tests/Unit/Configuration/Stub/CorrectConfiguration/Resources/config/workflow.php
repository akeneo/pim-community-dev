<?php

use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowStepType;
use Oro\Bundle\WorkflowBundle\Form\Type\WorkflowTransitionType;

return array(
    'first_workflow' => array(
        'label' => 'First Workflow',
        'enabled' => true,
        'start_step' => 'first_step',
        'steps' => array(
            'first_step' => array(
                'label' => 'First Step',
                'template' => 'My:Custom:template.html.twig',
                'order' => 1,
                'is_final' => true,
                'form_type' => WorkflowStepType::NAME,
                'form_options' => array(
                    'attribute_fields' => array(
                        'first_attribute' => array(
                            'label' => 'First Attribute',
                            'form_type' => 'text',
                            'options' => array(
                                'required' => 1
                            )
                        )
                    )
                ),
                'allowed_transitions' => array('first_transition'),
                'view_attributes' => array(
                    array('attribute' => 'first_attribute', 'view_type' => 'custom_view_type'),
                    array('path' => '$first_attribute.name', 'label' => 'Custom label'),
                    array('attribute' => 'first_attribute'),
                ),
            )
        ),
        'attributes' => array(
            'first_attribute' => array(
                'label' => 'First Attribute',
                'type' => 'object',
                'options' => array(
                    'class' => 'DateTime'
                )
            )
        ),
        'transitions' => array(
            'first_transition' => array(
                'label' => 'First Transition',
                'step_to' => 'first_step',
                'is_start' => true,
                'is_hidden' => true,
                'is_unavailable_hidden' => true,
                'message' => 'Test message',
                'transition_definition' => 'first_transition_definition',
                'frontend_options' => array(
                    'class' => 'foo'
                ),
                'form_type' => 'custom_workflow_transition',
                'form_options' => array(
                    'attribute_fields' => array(
                        'first_attribute' => array(
                            'label' => 'First Attribute',
                            'form_type' => 'text',
                            'options' => array(
                                'required' => 1
                            )
                        )
                    )
                ),
            )
        ),
        'transition_definitions' => array(
            'first_transition_definition' => array(
                'pre_conditions' => array(
                    '@true' => null
                ),
                'conditions' => array(
                    '@and' => array(
                        '@true' => null,
                        '@or' => array(
                            'parameters' => array(
                                '@true' => null,
                                '@equals' => array(
                                    'parameters' => array(1, 1),
                                    'message' => 'Not equals'
                                )
                            )
                        ),
                        'message' => 'Fail upper level'
                    )
                ),
                'post_actions' => array(
                    array(
                        '@custom_post_action' => null

                    )
                )
            )
        ),
        'type' => 'entity'
    ),
    'second_workflow' => array(
        'label' => 'Second Workflow',
        'enabled' => false,
        'start_step' => 'second_step',
        'steps' => array(
            'second_step' => array(
                'label' => 'Second Step',
                'template' => null,
                'order' => 1,
                'is_final' => false,
                'form_type' => 'custom_workflow_step',
                'allowed_transitions' => array(),
                'form_options' => array(),
                'view_attributes' => array(),
            )
        ),
        'attributes' => array(),
        'transitions' => array(
            'second_transition' => array(
                'label' => 'Second Transition',
                'step_to' => 'second_step',
                'is_start' => false,
                'is_hidden' => false,
                'is_unavailable_hidden' => false,
                'transition_definition' => 'second_transition_definition',
                'frontend_options' => array(
                    'icon' => 'bar'
                ),
                'form_type' => WorkflowTransitionType::NAME,
                'form_options' => array(),
            )
        ),
        'transition_definitions' => array(
            'second_transition_definition' => array(
                'pre_conditions' => array(),
                'conditions' => array(),
                'post_actions' => array()
            )
        ),
        'type' => 'wizard'
    )
);
