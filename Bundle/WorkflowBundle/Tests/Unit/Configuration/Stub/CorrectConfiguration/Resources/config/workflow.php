<?php

return array(
    'first_workflow' => array(
        'label' => 'First Workflow',
        'enabled' => 1,
        'start_step' => 'first_step',
        'steps' => array(
            'first_step' => array(
                'label' => 'First Step',
                'template' => 'My:Custom:template.html.twig',
                'order' => 1,
                'is_final' => 1,
                'form_type' => 'oro_workflow_step',
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
                'transition_definition' => 'first_transition_definition'
            )
        ),
        'transition_definitions' => array(
            'first_transition_definition' => array(
                'conditions' => array(
                    '@true' => null
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
            )
        ),
        'attributes' => array(),
        'transitions' => array(
            'second_transition' => array(
                'label' => 'Second Transition',
                'step_to' => 'second_step',
                'transition_definition' => 'second_transition_definition'
            )
        ),
        'transition_definitions' => array(
            'second_transition_definition' => array(
                'conditions' => array(),
                'post_actions' => array()
            )
        ),
        'type' => 'wizard'
    )
);
