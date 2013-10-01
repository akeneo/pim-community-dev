Transition Post Actions
=======================

Table of Contents
-----------------
 - [Add Custom Post Action](#add-custom-post-action)
 - [Configuration Syntax](#configuration-syntax)
 - [Assign Value](#assign-value)
 - [Unset Value](#unset-value)
 - [Create Entity](#create-entity)
 - [Find Entity](#find-entity)
 - [Start Workflow](#start-workflow)
 - [Close Workflow](#close-workflow)
 - [Redirect](#redirect)
 - [Redirect To Workflow](#redirect-to-workflow)
 - [Tree Executor](#tree-executor)
 - [Configurable](#configurable)

Add Custom Post Action
----------------------

To add custom post action add a service to DIC with tag "oro_workflow.post_action", for example:

```
parameters:
    oro_workflow.post_action.close_workflow.class: Oro\Bundle\WorkflowBundle\Model\PostAction\CloseWorkflow
services:
    oro_workflow.post_action.close_workflow:
        class: %oro_workflow.post_action.close_workflow.class%
        tags:
            - { name: oro_workflow.post_action, alias: close_workflow }
```

Symbol "|" in alias can be used to have several aliases. Note that service class must implement
Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface.

Configuration Syntax
--------------------

Each post action can be optionally configured with condition. It allows to implement more sufficient logic in
transitions definitions. If condition is not satisfied post action won't be executed.

See syntax examples:

**Full Configuration Example**

```
- @alias_of_post_action:
    condition:
        # optional condition configuration
    parameters:
        - some_parameters: some_value
        # other parameters of post action
    break_on_failure: boolean # by default false
```

**Short Configuration Example**
```
- @alias_of_post_action:
    - some_parameters: some_value
    # other parameters of post action
```

Assign Value
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\AssignValue

**Alias:** assign_value

**Description:** Sets value of attribute from source

**Parameters:**
 - attribute / 0 - attribute where value should be set;
 - value / 1 - value that should be set.

**Configuration Example**
```
- @assign_value:
    condition:
        # optional condition configuration
    parameters: [$call_successfull, true]

OR

- @assign_value:
    parameters:
        attribute: $call_successfull
        value: true
OR

- @assign_value: [$call_successfull, true]
```

Unset Value
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\UnsetValue

**Alias:** unset_value

**Description:** Unsets value of attribute from source

**Parameters:**
 - attribute / 0 - attribute where value should be set;

**Configuration Example**
```
- @unset_value:
    condition:
        # optional condition configuration
    parameters: [$call_successfull]

OR

- @unset_value:
    parameters:
        attribute: $call_successfull
OR

- @unset_value: [$call_successfull]
```

Create Entity
-------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\CreateEntity

**Alias:** create_entity

**Description:** Creates entity with specified class and data, and sets it as attribute value.

**Parameters:**
 - class - fully qualified class name of created entity;
 - attribute - attribute that will contain entity instance;
 - data - array of data that should be set to entity.

**Configuration Example**
```
- @create_entity:
    condition:
        # optional condition configuration
    parameters:
        class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation
        attribute: $conversation
        data:
            result: $conversation_result
            comment: $conversation_comment
            successful: $conversation_successful
            call: $managed_entity

OR

- @create_entity:
    class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation
    attribute: $conversation
    data:
        result: $conversation_result
        comment: $conversation_comment
        successful: $conversation_successful
        call: $managed_entity

```

Find Entity
-----------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\RequestEntity

**Alias:** find_entity|request_entity

**Description:** Finds entity by identifier value and assigns saves reference to path.

**Parameters:**
 - class - fully qualified class name of requested entity;
 - identifier - value of identifier of entity to find
 - attribute - target path where result of post action will be saved.

**Configuration Example**
```
- @find_entity:
    condition:
        # optional condition configuration
    parameters:
        class: OroCRM\Bundle\SalesBundle\Entity\LeadStatus
        identifier: 'canceled'
        attribute: $lead.status

OR

- @find_entity:
    class: OroCRM\Bundle\SalesBundle\Entity\LeadStatus
    identifier: 'canceled'
    attribute: $lead.status

```

Call Method
-----------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\CallMethod

**Alias:** call_method

**Description:** Triggers call of object method with parameters.

**Parameters:**
 - object - path to callee object
 - method - name of method to call
 - method_parameters - list of parameters that will be passed to method call.

**Configuration Example**
```
- @call_method:
    condition:
        # optional condition configuration
    parameters:
        object: $lead.contact
        method: addAddress
        method_parameters: [$.result.address]

OR

- @call_method: # add Address to Contact
    object: $lead.contact
    method: addAddress
    method_parameters: [$.result.address]

```

Start Workflow
--------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\StartWorkflow

**Alias:** start_workflow

**Description:** Triggers start of workflow with configured data. As a result a new WorkflowItem will be produced.

**Parameters:**
 - name - name of Workflow to start
 - attribute - path where result WorkflowItem will be saved
 - entity - path to entity that plays role of managed entity in started Workflow (optional)
 - transition - name of start transition (optional)

**Configuration Example**
```
- @start_workflow: # start workflow and create workflow item
    condition:
        # optional condition configuration
    parameters:
        name: sales
        attribute: $.result.workflowItem
        entity: $.result.opportunity
        transition: develop

OR

- @start_workflow: # start workflow and create workflow item
    name: sales
    attribute: $.result.workflowItem
    entity: $.result.opportunity
    transition: develop
```

Close Workflow
--------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\CloseWorkflow

**Alias:** close_workflow

**Description:** Triggers closing of current workflow item.

**Parameters:**
 - workflow_item / 0 - path to instance of Workflow Item
 - route - name of the route (by default route "oro_workflow_step_edit")
 - route_parameters - additional parameters of route

**Configuration Example**
```
- @close_workflow: ~
```

Redirect
--------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\Redirect

**Alias:** redirect

**Description:** Redirects unset to some route

**Parameters:**
 - url - URL where user should be redirected
 - route - name of the route, if set than url parameter will be ignored
 - route_parameters - parameters of route

**Configuration Example**
```
- @redirect:
    parameters:
        url: http://google.com

OR

- @redirect:
    url: http://google.com

OR

- @redirect:
    parameters:
        route: some_route_name
        route_parameters: {id: $some_entity.id}
```

Redirect To Workflow
--------------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\RedirectToWorkflow

**Alias:** redirect_to_workflow

**Description:** Redirects to another workflow item.

**Parameters:**
 - workflow_item \ 0 - path where instance of Workflow Item is located

**Configuration Example**
```
- @redirect_to_workflow:
    parameters:
        workflow_item: $.result.workflowItem

OR

- @redirect_to_workflow: [$.result.workflowItem]
```

Tree Executor
-------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\TreeExecutor

**Alias:** tree

**Description:** Composite object contains a list of post actions that will be executed sequentially.
If flag "breakOnFailure" is specified post action throws an exception on error, otherwise logs error using standard
logger.

**Configuration Example**
```
- @tree:
    condition:
        # optional condition configuration
    post_actions:
        - @create_entity:
            # post action configuration here
        - @tree:
            # post action configuration here
        # other post action

OR

- @tree:
    - @create_entity:
        # post action configuration here
    - @tree:
        # post action configuration here
    # other post action

```

Configurable
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\Configurable

**Alias:** configurable

**Description:** Proxy that requires configuration and builds list of Post Actions
on first invocation of "execute" method. Builds Post Actions tree using Post Action Assembler.
This post action is NOT intended to be used in configuration of Workflow,
but it can be used to create Post Actions based on configuration in runtime.

**Parameters:** Receives configuration array as source data.

**Code Example**
```php
$configuration = array(
    array(
        '@create_entity' => array(
            'parameters' => array('class' => 'TestClass', 'attribute' => '$entity'),
        ),
    ),
    array(
        '@assign_value' => array(
            'parameters' => array('$contact.name', 'name'),
        )
    ),
);

/** @var ConfigurablePostAction $configurablePostAction */
$configurablePostAction = $postActionFactory->create(Configurable::ALIAS, $configuration);

$configurablePostAction->execute($context); // build list of post actions and execute them
```
