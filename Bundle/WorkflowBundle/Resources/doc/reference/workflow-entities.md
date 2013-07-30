Workflow Entities
=================

Table of Contents
-----------------
 - [Main Entities](#main-entities)
   - [Workflow](#workflow)
   - [Workflow Registry](#workflow-registry)
   - [Step](#step)
   - [Transition](#transition)
   - [Attribute](#attribute)
   - [Condition](#condition)
   - [Condition Factory](#condition-factory)
   - [Post Action](#post-action)
   - [Post Action Factory](#post-action-factory)
 - [Entity Assemblers](#entity-assemblers)
   - [Workflow Assembler](#workflow-assembler)
   - [Step Assembler](#step-assembler)
   - [Transition Assembler](#transition-assembler)
   - [Attribute Assembler](#attribute-assembler)
   - [Condition Assembler](#condition-assembler)
   - [Post Action Assembler](#post-action-assembler)
   - [Post Action Assembler](#post-action-assembler)
 - [Database Entities](#database-entities)
   - [Workflow Definition](#workflow-definition)
   - [Workflow Definition Repository](#workflow-definition-repository)
   - [Workflow Item](#workflow-item)
   - [Workflow Item Repository](#workflow-item-repository)
   - [Workflow Item Entity](#workflow-item-repository)
 - [Support Entities](#support-entities)
   - [Workflow Data](#workflow-data)
   - [Entity Binder](#entity-binder)
   - [Configuration Tree](#configuration-tree)
   - [Configuration Provider](#configuration-provider)
   - [Workflow Data Serializer](#workflow-data-serializer)
   - [Workflow Data Normalizer](#workflow-data-normalizer)
   - [Attribute Normalizer](#attribute-normalizer)
   - [Parameter Pass](#parameter-pass)

Main Entities
=============
Workflow
--------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Workflow

**Description:**
Encapsulates all logic of workflow, contains lists of steps, attributes and transitions, knows about managed entity class, start step and EntityBinder.

**Methods:**
* **transit(WorkflowItem, transitionName)** - performs transit with name transitionName for specified WorkflowItem;
* **isTransitionAllowed(WorkflowItem, Transition)** - calculates whether Transition is allowed for specified WorkflowItem;
* **createWorkflowItem(Entity)** - creates WorkflowItem for specified Entity.

Workflow Registry
-----------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry

**Description:** Assembles Workflow items using WorkflowAssembler, stores it in internal cache and return Workflow items by their names.

**Methods:**
* **getWorkflow(workflowName)** - extracts Workflow item by its name.

Step
----
**Class:**
Oro\Bundle\WorkflowBundle\Model\Step

**Description:**
Encapsulated step parameters, contains lists of attributes and allowed transition names, has step template and isFinal flag.

**Methods:**
* **isAllowedTransition(transitionName)** - calculates whether transition with name transitionName allowed for current step;
* **allowTransition(transitionName)** - allow transition with name transitionName;
* **disallowTransition(transitionName)** - disallow transition with name transitionName.

Transition
----------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Transition

**Description:**
Encapsulates transition parameters, contains condition and post action, has next step property.

**Methods:**
* **isAllowed(WorkflowItem)** - calculates whether this transition allowed for WorkflowItem;
* **transit(WorkflowItem)** - performs transition for WorkflowItem.

Attribute
---------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Attribute

**Description:**
Encapsulates attribute parameters, has label, form type and options.

Condition
--------
**Interface:**
Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface

**Description:**
Basic interface for Transition Conditions. Detailed description

**Methods:**
* **initialize(options)** - initialize specific condition based on input options;
* **isAllowed(context)** - calculates whether specific condition is allowed for current context (usually context is WorkflowItem).

Condition Factory
-----------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory

**Description:**
Creates instances of Transition Conditions based on type (alias) and options.

**Methods:**
* **create(type, options)** - creates specific instance of Transition Condition.

Post Action
----------
**Interface:**
Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionInterface

**Description:**
Basic interface for Transition Post Actions. Detailed description

**Methods:**
* **initialize(options)** - initialize specific post action based on input options;
* **execute(context)** - execute specific post action for current context (usually context is WorkflowItem).

Post Action Factory
-------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionFactory

**Description:**
Creates instances of Transition Post Actions based on type (alias) and options.

**Methods:**
* **create(type, options)** - creates specific instance of Transition Post Action.

Entity Assemblers
=================

Workflow Assembler
------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowAssembler

**Description:**
Creates instances of Wokflows based on Workflow Definitions. Requires configuration tree to parse configuration and attribute, step and transition assemblers to assemble appropriate parts of configuration.

**Methods:**
* **assemble(WorkflowDefinition)** - assemble and returns Workflow instance based on input WorkflowDefinition.

Step Assembler
--------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\StepAssembler

**Description:**
Creates instances of Steps based on input configuration and Attributes.

**Methods:**
* **assemble(configuration, attributes)** - assemble and returns list of Step instances.

Transition Assembler
--------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\TransitionAssembler

**Description:**
Creates instances of Transitions based on transition configuration, transition definition configuration and list of Step entities. Uses Condition Factory and Post Action Factory to create configurable conditions and post actions.

**Methods:**
* **assemble(configuration, definitionsConfiguration, steps)** - assemble and returns list of Transitions.

Attribute Assembler
-------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\AttributeAssembler

**Description:**
Assemble Attribute instances based on source configuration.

**Methods:**
* **assemble(configuration)** - assemble and returns list of Atributes.

Condition Assembler
-------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Condition\ConditionAssembler

**Description:**
Recursively walks through Condition configuration and creates instance of appropriate Conditions using Condition Factory.

**Methods:**
assemble(configuration) - assemble configuration and returns root Condition instance.

Post Action Assembler
---------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\PostAction\PostActionAssembler

**Description:**
Walks through Post Action configuration and creates instance of appropriate Post Actions using Post Action Factory.

**Methods:**
* **assemble(configuration)** - assemble configuration and returns instance of list Post Action.

Database Entities
=================

Workflow Definition
-------------------

**Class:**
Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition

**Description:**
Encapsulates Workflow parameters and serialized array with configuration.

Workflow Definition Repository
------------------------------

**Class:**
Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowDefinitionRepository

**Methods:**
* **findWorkflowDefinitionsByEntity(entity)** - returns list of appropriate Workflow Definitions for input entity.

Workflow Item
-------------
**Class:**
Oro\Bundle\WorkflowBundle\Entity\WorkflowItem

**Description:**
Specific instance of Worklflow, contains state of workflow - data as instance of WorkflowData, current step name and list of related entities as list of WorkflowItemEntity entities.

**Methods:**
* **addEntity(WorkflowItemEntity)** - add new instance of related entity;
* **removeEntity(WorkflowItemEntity)** - remove existing related entity.

Workflow Item Repository
------------------------
**Class:**
Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository

**Methods:**
* **findWorkflowItemsByEntity(entity)** - returns list of all Workflow Items related to input entity.

Workflow Item Entity
--------------------
**Class:**
Oro\Bundle\WorkflowBundle\Entity\WorkflowItemEntity

**Description:**
Encapsulates relation of Workflow Item with specific entity, contains entity ID, entity class name and step name of Workflow.

Support Entities
================
Workflow Data
-------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowData

**Description:**
Container for all Workflow data, implements ArrayAccess, IteratorAggregate and Countable interfaces.

Entity Binder
-------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\EntityBinder

**Description:**
Bind specific entity to Workflow Item with specific step, extracts entity class name and entity ID using Doctrine metadata.

**Methods:**
* **bind(WorkflowItem, entity, assignedStep)** - bind entity to WorkflowItem with assigned step.

Configuration Tree
------------------
**Class:**
Oro\Bundle\WorkflowBundle\Configuration\ConfigurationTree

**Description:**
Contains tree builders for configuration parts (steps, conditions, condition definitions, transitions), provides list of node definitions and function to parse data.

**Methods:**
* **parseConfiguration(configuration)** - parses input configuration according to generated node definitions;
* **getNodeDefinitions()** - returns list of node definitions from tree builders.

Configuration Provider
---------------------
**Class:**
Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider

**Description:**
Parses files workflow.yml in all bundles, builds configuration using Configuration Tree and creates instances of Workflow Definitions.

**Methods:**
* **getWorkflowDefinitions()** - returns list of Workflow Definitions that were built using configuration files in all bundles.

Workflow Data Serializer
-----------------------
**Interface:**
Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer

**Class:**
Oro\Bundle\WorkflowBundle\Serializer\WorkflowDataSerializer

**Description:**
Extends standard Symfony Serializer to support Workflow entities.

Workflow Data Normalizer
------------------------
**Class:**
Oro\Bundle\WorkflowBundle\Serializer\Normalizer\WorkflowDataNormalizer

**Description:**
Custom data normalizer for Workflow Data Serializer, use basic serializer and Attribute Serializer.

**Methods:**
* **normalize(object, format, context)** - convert complex source data to plain representation;
* **denormalize(data, class, format, context)** - convert plain source data to complex representation.

Attribute Normalizer
--------------------
**Class:**
Oro\Bundle\WorkflowBundle\Serializer\Normalizer\AttributeNormalizer

**Description:**
Custom data normalizer for Attribute entities, convert entity values to plain representation.

**Methods:**
* **normalize(workflow, attributeName, attributeValue)** - convert Workflow Attribute value to plain representation;
* **denormalize(workflow, attributeName, attributeValue)** - convert Workflow Attribute value to original representation.

Parameter Pass
--------------
**Interface:**
Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface

**Class:**
Oro\Bundle\WorkflowBundle\Model\Pass\ParameterPass

**Description:**
Passes through configuration and replaces access properties (f.e. $property) with appropriate PropertyPath intstances.

**Methods:**
* **pass(data)** - replaces access properties with Property Path instances.
