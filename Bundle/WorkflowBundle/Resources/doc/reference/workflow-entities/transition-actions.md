Transition Actions
=======================

Table of Contents
-----------------
 - [Add Custom Action](#add-custom-action)
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

Add Custom Action
----------------------

To add custom action add a service to DIC with tag "oro_workflow.action", for example:

```
parameters:
    oro_workflow.action.close_workflow.class: Oro\Bundle\WorkflowBundle\Model\Action\CloseWorkflow
services:
    oro_workflow.action.close_workflow:
        class: %oro_workflow.action.close_workflow.class%
        tags:
            - { name: oro_workflow.action, alias: close_workflow }
```

Symbol "|" in alias can be used to have several aliases. Note that service class must implement
Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface.

Configuration Syntax
--------------------

Each action can be optionally configured with condition. It allows to implement more sufficient logic in
transitions definitions. If condition is not satisfied action won't be executed.

If flag "break_on_failure" is specified action throws an exception on error, otherwise logs error using standard
logger.

See syntax examples:

**Full Configuration Example**

```
- @alias_of_action:
    conditions:
        # optional condition configuration
    parameters:
        - some_parameters: some_value
        # other parameters of action
    break_on_failure: boolean # by default false
```

**Short Configuration Example**
```
- @alias_of_action:
    - some_parameters: some_value
    # other parameters of action
```

Assign Value
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\AssignValue

**Alias:** assign_value

**Description:** Sets value of attribute from source

**Parameters:**
 - attribute / 0 - attribute where value should be set;
 - value / 1 - value that should be set.

**Configuration Example**
```
- @assign_value:
    conditions:
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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\UnsetValue

**Alias:** unset_value

**Description:** Unsets value of attribute from source

**Parameters:**
 - attribute / 0 - attribute where value should be set;

**Configuration Example**
```
- @unset_value:
    conditions:
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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\CreateEntity

**Alias:** create_entity

**Description:** Creates entity with specified class and data, and sets it as attribute value.

**Parameters:**
 - class - fully qualified class name of created entity;
 - attribute - attribute that will contain entity instance;
 - data - array of data that should be set to entity.

**Configuration Example**
```
- @create_entity:
    conditions:
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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\RequestEntity

**Alias:** find_entity|request_entity

**Description:** Finds entity by identifier value or "where" condition and saves reference or entity to path.

**Parameters:**
 - class - fully qualified class name of requested entity;
 - attribute - target path where result of action will be saved;
 - identifier - value of identifier of entity to find;
 - where - array of conditions to find entity, key is field name, value is scalar value or path;
 - order_by - array of fields used to sort values, key is field name, value is direction (asc or desc);
 - case_insensitive - boolean flag used to find entity using case insensitive search, default value is false.

**Configuration Example**
```
- @find_entity:
    conditions:
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

OR

- @find_entity: # try to find account by company name
    class: OroCRM\Bundle\AccountBundle\Entity\Account
    attribute: $account
    where:
        name: $company_name
    order_by:
        date_created: desc
    case_insensitive: true
```

Call Method
-----------

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\CallMethod

**Alias:** call_method

**Description:** Triggers call of object method with parameters.

**Parameters:**
 - object - path to callee object
 - method - name of method to call
 - method_parameters - list of parameters that will be passed to method call.

**Configuration Example**
```
- @call_method:
    conditions:
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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\StartWorkflow

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
    conditions:
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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\CloseWorkflow

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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\Redirect

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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\RedirectToWorkflow

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

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\TreeExecutor

**Alias:** tree

**Description:** Composite object contains a list of actions that will be executed sequentially.

**Configuration Example**
```
- @tree:
    conditions:
        # optional condition configuration
    actions:
        - @create_entity:
            # action configuration here
        - @tree:
            # action configuration here
        # other action

OR

- @tree:
    - @create_entity:
        # action configuration here
    - @tree:
        # action configuration here
    # other action

```

Configurable
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\Action\Configurable

**Alias:** configurable

**Description:** Proxy that requires configuration and builds list of actions
on first invocation of "execute" method. Builds actions tree using action Assembler.
This action is NOT intended to be used in configuration of Workflow,
but it can be used to create actions based on configuration in runtime.

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

/** @var ConfigurableAction $configurableAction */
$configurableAction = $actionFactory->create(Configurable::ALIAS, $configuration);

$configurableAction->execute($context); // build list of actions and execute them
```
