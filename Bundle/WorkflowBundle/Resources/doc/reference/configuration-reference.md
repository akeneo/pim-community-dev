Overview
========

Configuration of Workfow declares all aspects related to specific workflow:
* basic properties of workflow like name and label
* steps and transitions
* attributes involved in workflow
* entities that are related to workflow

Configuration File
==================

Configuration must be placed in a file named Resources/config/workflow.yml. For example src/Acme/DemoWorkflowBundle/Resources/config/workflow.yml.

Configuration Loading
=====================

To load configuration execute a command
```
php app/console doctrine:fixtures:load --app/console doctrine:fixtures:load --append --fixtures=/path/to/bundles/WorkflowBundle/DataFixture
```
**Warning** --append options is crucial, if you skip it your data will be lost.

This command will save configuration from all workflow.yml files of loaded bundles into WorkflowDefinition entity. It can be used in both cases when you want to load a new workflow or update existed one.

**Important**
Workflow configuration cannot be merged, it means that you cannot override workflow that is defined in other bundle. If you will declare a workflow and another bundle will declare it's own workflow with the same name the command will trigger exception and data won't be saved.

Defining a Workflow
===================

Configuration root element is workflows. Under this element workflows can be defined.

A workflow configuration by itself has next properties:

* **label**
This value will be shown in the UI

* **enabled**
A flag is workflow enabled or not. If not enabled, operations with workflow will be be restricted.

* **start_step**
The name of first steo

* **managed_entity_class**
**Warning** This options will be replaced with a flag of attribute in nearest release.

    The fully qualified class name of entity that is managed by workflow. It is optional, but if it's set Workflow Item must be created with an entity of this class:

    ```
    $workflowItem = $workflow->createWorkflowItem($entity);
    ```

    Also when this option is set next features are available:
    * it's data have a property named 'managed_entity' and any workflow condition or post action can use it for their purposes, for example

    ```
    conditions:
    @not_blank: [$managed_entity.someProperty]
    post_actions:
    - @assign_value: [$managed_entity.someProperty, "Some value"]
    ```

    * entity is binded with workflow item, it means that you can find workflow item by this entity like this:
    ```
    /** @var \Oro\Bundle\WorkflowBundle\Entity\WorkflowItem $workflowItems */
    $workflowItems = $em->getRepository('OroWorkflowBundle:WorkflowItem')->findWorkflowItemsByEntity($entity)
    ```
    it can be useful if you wan't to show all workflows that are related to some entity.

    * all workflows that manage some entity class can be found
    ```
    /** @var \Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry $workflowRegistry */
    $workflowRegistry = $this->get('oro_workflow.registry');
    /** @var \Oro\Bundle\WorkflowBundle\Model\Workflow $applicableWorkflows */
    $applicableWorkflows = $workflowRegistry->getWorkflowsByEntity($phoneCall);
    ```

* **attributes**
Contains configuration for Attributes

* **steps**
Contains configuration for Steps

* **transitions**
Contains configuration for Transition

* **transition_definitions**
Contains configuration for Transition Definitions

Example
-------
```
workflows:
    phone_call: # A unique name of workflow
        label: Demo Call Workflow # This will be shown in UI
        enabled: true # If not enabled, operations with workflow will be restricted.
        start_step: start_call #
        managed_entity_class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneCall # Optional, connects workflow and Entity
        attributes:
            # configuration for Attributes here
        steps:
            # configuration for Steps here
        transitions: #
            # configuration for Transitions here
        transition_definitions: #
            # configuration for Transition Definitions here
```

Attributes Configuration
========================

Workflow define schema of attributes. When Workflow Item is created it can manipulate it's own data that is mapped by Attributes.

**Warning** Currently any value can be saved in Workflow Data and it can be not mapped with Attribute, but in nearest release it will be restricted to set data to Workflow Item that is not mapped by Attribute.

**Warning** Currently attribute owns information about Form Type that is used to display in form of Step, but in nearest release this responsibility will be moved to separate model and attribute by itself will not know how to render it.

Attribute described with next configuration:

* **unique name**
Workflow attributes should have unique name in scope of Workflow that they belong to. Step configuration references attributes by this value.

* **form_type**
Name of Form Type that represents this attribute

* **label**
Label can be shown in the UI

* **options**
Options of this attribute. Currently two options are supported: "entity_class" and "form_options"
    * **entity_class**
    If this attribute refers to some entity, it's fully qualified class name should be specified here

    * **form_options**
    Use options that are compatible with the Form Type that you have specified in form_type option

Example
-------

```
attributes:
    call_timeout: # Name of attribute unique in the scope of Workflow
        form_type: integer # Refers to Form Type in Symfony
        label: 'Call Timeout' # Can be shown in UI
        options:
            form_options: # Options compatible with Form Type
                required: false
    conversation_comment:
        form_type: text
        label: 'Conversation Comment'
    conversation_result:
        form_type: text
        label: 'Conversation Result'
    conversation:
        form_type: entity
        label: 'Conversation'
        options:
            entity_class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation # Connect this attribute to Entity
```

Steps configuration
===================

Step in workflow is a central model. After Workflow Item is created it's current step equals to start step declared in Workflow. Step can optionally contain attributes and allowed transitions.

All attributes that are related to step will be displayed in the form of Workflow Item and User will be able to change values of this attributes. If attribute doesn't belong to any Step it means that User cannot change it's value directly but conditions and post actions can manipulate it.

Summarizing all above, step has next configuration:

* **unique name**
Step must have unique name in scope of Workflow

* **label**
Can be shown in UI

* **attributes**
Optional list of attributes

* **allowed_transitions**
Optional list of allowed transitions. If no transitions are allowed it's same as is_final option set to true
* **is_final**
If set to TRUE no transitions will be allowed, no matter of allowed_transitions option

Example
-------

```
steps:
    start_call: # Unique name is scope of Workflow
        label: 'Start Phone Call'
        attributes: # List of attributes (optional)
            - call_timeout
        allowed_transitions:
            - connected
            - not_answered
    start_conversation:
        label: 'Call Phone Conversation'
        attributes:
            - conversation_result
            - conversation_comment
            - conversation_successful
        allowed_transitions:
            - end_conversation
    end_call:
        label: 'End Phone Call'
        is_final: true
```

Transitions Configuration
=========================

Transitions changes current step of Workflow Item when it's performed. It also uses Transition Defintion to check if it's allowed and to perform Post Actions. Transition configuration has next options:

* **unique name**
A transition must have unique name in scope of Workflow. Step configuration references transitions by this value.

* **step_to**
Next step name. This is a reference to step that will be set to Workflow Item after transition is performed.

* **transition_definition**
A references to Transition Definition configuration

Example
-------

```
transitions:
    connected: # Unique name of transition
        label: 'Connected' # Label can be used in UI
        step_to: start_conversation # The name of next step that will be set to Workflow Item when transition will be performed
        transition_definition: connected_definition # A reference to Transition Definition configuration
    not_answered:
        label: "Not answered"
        step_to: end_call
        transition_definition: not_answered_definition
    end_conversation:
        label: 'End conversation'
        step_to: end_call
        transition_definition: end_conversation_definition
```

Transition Definition Configuration
===================================

Transition Definition is used by Transition to check Conditions and to perform Post Actions.

Transition definition configuration has next options.

* **conditions**
Configuration of Conditions that must satisfy to allow transition

* **post_actions**
Configuration of Post Actions that must be performed after transit to next step will be performed.

Example
-------

```
transition_definitions:
    connected_definition: # Unique name of transition definition
        # Configuration of conditions (optional)
        conditions:
            @not_blank: [$call_timeout]
        # Configuration of Post Actions (optional)
        post_actions:
            - @assign_value:
                parameters: [$call_successfull, true]
```

Conditions Configuration
========================

Conditions configuration is a part of Transition Definition Configuration. It declares a tree structure of conditions that are applied on the Workflow Item to check is the Transition could be performed. Single condition configuration contains from alias - a unique name of condition and options.

Alias of condition starts from "@" symbol and must refer to registered condition. For example "@or" refers to logical OR condition.
Options can refer to values of Workflow Data using "$" prefix. For example "$call_timeout" refers to value of "call_timeout" attribute of Workflow Item that is processed in condition.

Example
-------

```
conditions:
    # empty($call_timeout) || (($call_timeout >= 60 && $call_timeout < 100) || ($call_timeout > 0 && $call_timeout <= 30))
    @or:
        - @blank: [$call_timeout]
        - @or:
            - @and:
                - @greater_or_equal: [$call_timeout, 60]
                - @less: [$call_timeout, 100]
            - @and:
                - @less_or_equal: [$call_timeout, 30]
                - @greater: [$call_timeout, 0]
```

Post Actions
============

Post actions configuration complements Transition Definition configuration. All configured Post Actions will be performed during transition AFTER conditions will be qualified and current Step of Workflow Item will be changed to the corresponding in the Transition.
Single Post Action configuration consists from alias of Post Action (which is a unique name of Post Action) and options (if such are required).

Similarly to Conditions alias of Post Action starts from "@" symbol and must refer to registered PostAction. For example "@create_entity" refers to Post Action which creates entity.

Example
-------

```
post_actions:
    - @create_entity: # create an entity PhoneConversation
        parameters:
            class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation
            attribute: $conversation
            data: # Fill values of freshly created PhoneConversation with data from current WorkflowItem
                result: $conversation_result
                comment: $conversation_comment
                successful: $conversation_successful
                call: $managed_entity
    - @bind_entity: # Bind created PhoneConversation with current workflow item. This is required if you want to find all Workflow Items by this entity
        parameters:
            attribute: $conversation
```

Example Workflow Configuration
==============================

An example of this Workflow can be found in Acme\Bundle\DemoWorkflowBundle.
There are two entities that are involved in this Workflow:

* Phone Call
* Phone Conversation

![Workflow Diagram](../images/configuration-reference_workflow-example-entities.png)

When Workflow Item is created it's connected to Phone Call. On the first step "Start Call" user can go to "Call Phone Conversation Step" if a callee answered or to "End Phone Call" step if callee didn't answer. On the step "Call Phone Conversation" User enters Worfklow Data and go to "End Phone Call" step via "End conversation" transition. On this transition a new Entiy of Phone Conversation is created and assigned to Phone Call entity.

Configuration
-------------

```
workflows:
    phone_call:
        label: 'Demo Call Workflow'
        enabled: true
        start_step: start_call
        managed_entity_class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneCall
        steps:
            start_call:
                label: 'Start Phone Call'
                attributes:
                    - call_timeout
                allowed_transitions:
                    - connected
                    - not_answered
            start_conversation:
                label: 'Call Phone Conversation'
                attributes:
                    - conversation_result
                    - conversation_comment
                    - conversation_successful
                allowed_transitions:
                    - end_conversation
            end_call:
                label: 'End Phone Call'
                is_final: true
        attributes:
            call_timeout:
                form_type: integer
                label: 'Call Timeout'
                options:
                    form_options:
                        required: false
            call_successfull:
                form_type: choice
                label: 'Call Successful'
                options:
                    form_options:
                        choices: {0: 'Yes', 1: 'No'}
                        required: true
                        multiple: false
            conversation_successful:
                form_type: choice
                label: 'Conversation Successful'
                options:
                    form_options:
                        choices: {0: 'Yes', 1: 'No'}
                        required: true
                        multiple: false
            conversation_comment:
                form_type: text
                label: 'Conversation Comment'
            conversation_result:
                form_type: text
                label: 'Conversation Result'
            conversation:
                form_type: entity
                label: 'Conversation'
                options:
                    entity_class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation
        transitions:
            connected:
                label: 'Connected'
                step_to: start_conversation
                transition_definition: connected_definition
            not_answered:
                label: "Not answered"
                step_to: end_call
                transition_definition: not_answered_definition
            end_conversation:
                label: 'End conversation'
                step_to: end_call
                transition_definition: end_conversation_definition
        transition_definitions:
            connected_definition: # Try to make call connected
                # Check that timeout is set
                conditions:
                    @not_blank: [$call_timeout]
                # Set call_successfull = true
                post_actions:
                    - @assign_value:
                        parameters: [$call_successfull, true]
            not_answered_definition: # Callee did not answer
                # Make sure that caller waited at least 60 seconds
                conditions: # call_timeout not empty and >= 60
                    @and:
                        - @not_blank: [$call_timeout]
                        - @ge: [$call_timeout, 60]
                # Set call_successfull = false
                post_actions:
                    - @assign_value:
                        parameters: [$call_successfull, false]
            end_conversation_definition:
                conditions:
                    # Check required properties are set
                    @and:
                        - @not_blank: [$conversation_result]
                        - @not_blank: [$conversation_comment]
                        - @not_blank: [$conversation_successful]
                # Create PhoneConversation and set it's properties
                # Pass data from workflow to conversation
                post_actions:
                    - @create_entity: # create PhoneConversation
                        parameters:
                            class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation
                            attribute: $conversation
                            data:
                                result: $conversation_result
                                comment: $conversation_comment
                                successful: $conversation_successful
                                call: $managed_entity
                    - @bind_entity: # bind created PhoneConversation with current workflow item
                        parameters:
                            attribute: $conversation
```

Flow Diagram
------------

![Workflow Diagram](../images/configuration-reference_workflow-example-diagram.png)
