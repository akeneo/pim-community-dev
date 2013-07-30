Transition Post Actions
=======================

Table of Contents
-----------------
 - [Assign Value](#assign-value)
 - [Create Entity](#create-entity)
 - [Bind Entity](#bind-entity)
 - [List Executor](#list-executor)
 - [Configurable](#configurable)

Assign Value
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\AssignValue

**Alias:** assign_value

**Description:** Sets value of attribute from source

**Parameters:**
 - attribute / 0 - attribute where value should be set;
 - value / 1 - value that should be set.

**Configuration example**
```
- @assign_value:
    parameters: [$call_successfull, true]

OR

- @assign_value:
    parameters:
        attribute: $call_successfull
        value: true
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

**Configuration example**
```
- @create_entity:
    parameters:
        class: Acme\Bundle\DemoWorkflowBundle\Entity\PhoneConversation
        attribute: $conversation
        data:
            result: $conversation_result
            comment: $conversation_comment
            successful: $conversation_successful
            call: $managed_entity
```

Bind Entity
-----------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\BindEntity

**Alias:** bind_entity

**Description:** Bind entity to workflow item instance.

**Parameters:**
 - attribute - attribute name that contain entity to bound.

**Configuration example**
```
- @bind_entity:
    parameters:
        attribute: $conversation
```

List Executor
-------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\ListExecutor

**Alias:** list

**Description:** Executes all Post Actions that was added using method addPostAction.
If flag "breakOnFailure" is specified post action throws an exception on error,
otherwise logs error using standard logger. This Post Action can be used to create list of external Post Actions
and then referencing to it in configuration as to single Post Action.

**Code example**
```php
/** @var PostActionInterface $customPostAction */
$customPostAction = $postActionFactory->create('custom_post_action', $postActionParameters);

/** @var ListExecutor $listPostAction */
$listPostAction = $postActionFactory->create(ListExecutor::ALIAS);
$listPostAction->addPostAction($customPostAction);

$listPostAction->execute($context); // execute all nested post actions
```

Configurable
------------

**Class:** Oro\Bundle\WorkflowBundle\Model\PostAction\Configurable

**Alias:** configurable

**Description:** Proxy that requires configuration and builds list of Post Actions
on first invocation of "execute" method. Builds Post Actions list using Post Action Assembler.
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
$configurablePostAction = $postActionFactory->create(ConfigurablePostAction::ALIAS, $configuration);

$configurablePostAction->execute($context); // build list of post actions and execute them
```
