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
   - [Action](#action)
   - [Action Factory](#action-factory)
 - [Entity Assemblers](#entity-assemblers)
   - [Workflow Assembler](#workflow-assembler)
   - [Step Assembler](#step-assembler)
   - [Transition Assembler](#transition-assembler)
   - [Attribute Assembler](#attribute-assembler)
   - [Condition Assembler](#condition-assembler)
   - [Action Assembler](#action-assembler)
 - [Database Entities](#database-entities)
   - [Workflow Definition](#workflow-definition)
   - [Workflow Definition Repository](#workflow-definition-repository)
   - [Workflow Item](#workflow-item)
   - [Workflow Item Repository](#workflow-item-repository)
   - [Workflow Bind Entity](#workflow-bind-entity)
 - [Support Entities](#support-entities)
   - [Workflow Data](#workflow-data)
   - [Workflow Result](#workflow-result)
   - [Context Accessor](#context-accessor)
   - [Entity Binder](#entity-binder)
   - [Workflow Configuration](#workflow-configuration)
   - [Workflow List Configuration](#workflow-list-configuration)
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
Encapsulates all logic of workflow, contains lists of steps, attributes and transitions. Create instance of
Workflow Item, performs transition if it's allowed, gets allowed transitions and start transitions.
Delegates operations with aggregated domain models to corresponding managers, such as StepManager, TransitionManager
and AttributeManager

**Methods:**
* **transit(WorkflowItem, Transition)** - performs transit with name transitionName for specified WorkflowItem;
* **isTransitionAllowed(WorkflowItem, Transition)** - calculates whether transition is allowed for specified WorkflowItem;
* **start(data, Transition)** - returns new instance of Workflow Item and processes it's start transition.
* **getAllowedStartTransitions(data)** - returns a list of allowed start transitions
* **getAllowedTransitions(WorkflowItem)** - returns a list of allowed transitions
* **getTransition(transitionName)** - gets Transition by name
* **getTransitions(transitionName)** - gets all transitions
* **getBindEntityAttributes()** - gets list of Attributes of bound entities
* **getManagedEntityAttributes()** - gets list of Attributes of managed entities
* **getAttributes()** - gets list of all Attributes
* **getOrderedSteps()** - gets ordered list of all Steps
* **createWorkflowItem(array data)** - create WorkflowItem instance and initialize it with passed data

Workflow Registry
-----------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry

**Description:** Assembles Workflow object using WorkflowAssembler, and return Workflow objects by their names
or managed entities.

**Methods:**
* **getWorkflow(workflowName)** - extracts Workflow object by its name.
* **getWorkflowsByEntityClass(entityClass)** - gets list of Workflow objects where given entity class is managed entity.

Step
----
**Class:**
Oro\Bundle\WorkflowBundle\Model\Step

**Description:**
Encapsulated step parameters, contains lists of attributes and allowed transition names, has step template,
isFinal flag, form type and form options.

**Methods:**
* **isAllowedTransition(transitionName)** - calculates whether transition with name transitionName allowed for current step;
* **allowTransition(transitionName)** - allow transition with name transitionName;
* **disallowTransition(transitionName)** - disallow transition with name transitionName.

Transition
----------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Transition

**Description:**
Encapsulates transition parameters, contains init action, condition and post action, has next step property.

**Methods:**
* **isAllowed(WorkflowItem)** - calculates whether this transition allowed for WorkflowItem;
* **transit(WorkflowItem)** - performs transition for WorkflowItem.

Attribute
---------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Attribute

**Description:**
Encapsulates attribute parameters, has label, type and options.

Condition
---------
**Interface:**
Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface

**Description:**
Basic interface for Transition Conditions.

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

Action
-----------
**Interface:**
Oro\Bundle\WorkflowBundle\Model\Action\ActionInterface

**Description:**
Basic interface for Transition Actions. Detailed description

**Methods:**
* **initialize(options)** - initialize specific action based on input options;
* **execute(context)** - execute specific action for current context (usually context is WorkflowItem).

Action Factory
-------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Action\ActionFactory

**Description:**
Creates instances of Transition Actions based on type (alias) and options.

**Methods:**
* **create(type, options)** - creates specific instance of Transition Action.

Entity Assemblers
=================

Workflow Assembler
------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowAssembler

**Description:**
Creates instances of Wokflow onjects based on Workflow Definitions. Requires configuration object to parse
configuration and attribute, step and transition assemblers to assemble appropriate parts of configuration.

**Methods:**
* **assemble(WorkflowDefinition)** - assemble and returns instance of Workflow based on input WorkflowDefinition.

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
Creates instances of Transitions based on transition configuration, transition definition configuration and list of
Step entities. Uses Condition Factory and Action Factory to create configurable conditions and actions.

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

Action Assembler
---------------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\Action\ActionAssembler

**Description:**
Walks through Action configuration and creates instance of appropriate Actions using Action Factory.

**Methods:**
* **assemble(configuration)** - assemble configuration and returns instance of list Action.

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
* **findByEntityClass(entity)** - returns list of appropriate Workflow Definitions for input entity.

Workflow Item
-------------
**Class:**
Oro\Bundle\WorkflowBundle\Entity\WorkflowItem

**Description:**
Specific instance of Workflow, contains state of workflow - data as instance of WorkflowData,
temporary storage of result of last applied transition actions as instance of WorkflowResult, current step name,
list of related entities as list of WorkflowBindEntity entities, log of all applied transitions as list of
WorkflowTransitionRecord entities.

**Methods:**
* **addBindEntity(WorkflowBindEntity)** - adds new instance of related entity;
* **removeBindEntity(WorkflowBindEntity)** - removes existing related entity.

Workflow Item Repository
------------------------
**Class:**
Oro\Bundle\WorkflowBundle\Entity\Repository\WorkflowItemRepository

**Methods:**
* **findByEntityMetadata(entityClass, entityIdentifier, workflowName, workflowType)** - returns list of all Workflow
Items related to input parameters.

Workflow Bind Entity
--------------------
**Class:**
Oro\Bundle\WorkflowBundle\Entity\WorkflowBindEntity

**Description:**
Encapsulates relation of Workflow Item with specific entity, contains entity ID, entity class name and step name
of Workflow.

Support Entities
================
Workflow Data
-------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowData

**Description:**
Container for all Workflow data, implements ArrayAccess, IteratorAggregate and Countable interfaces.

Workflow Result
---------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\WorkflowResult

**Description:**
Container of results of last applied transition actions. This data is not persistable so it can be used only once
right after successful transition.

Context Accessor
----------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\ContextAccessor

**Description:**
Context is used in action and conditions and thereby it's usually an instance of Workflow Item.
This class is a simple helper that encapsulates logic of accessing properties of context using
Symfony\Component\PropertyAccess\PropertyAccessor.

Entity Binder
-------------
**Class:**
Oro\Bundle\WorkflowBundle\Model\EntityBinder

**Description:**
Ensures that all values of bind attributes of WorkflowItem are actually persisted or removed (if values was unset).
This class delegates operations with Doctrine classes to special helper - a class
Oro\Bundle\WorkflowBundle\Model\DoctrineHelper.

**Methods:**
* **bindEntities(WorkflowItem)** - bind all corresponding attribute values.

Workflow Configuration
----------------------
**Class:**
Oro\Bundle\WorkflowBundle\Configuration\WorkflowConfiguration

**Description:**
Contains tree builder for single Workflow configuration with steps, conditions, condition definitions, transitions.

**Methods:**
* **getConfigTreeBuilder()** - configuration tree builder for single Workflow configuration.

Workflow List Configuration
------------------
**Class:**
Oro\Bundle\WorkflowBundle\Configuration\WorkflowListConfiguration

**Description:**
Contains tree builder for list of Workflows, processConfiguration raw configuration of Workflows.

**Methods:**
* **getConfigTreeBuilder()** - configuration tree builder for list of Workflows.
* **processConfiguration(configs)** - processes raw configuration according to configuration tree builder

Configuration Provider
---------------------
**Class:**
Oro\Bundle\WorkflowBundle\Configuration\ConfigurationProvider

**Description:**
Parses files workflow.yml in all bundles and processes merged configuration using Workflow List Configuration.

**Methods:**
* **getWorkflowDefinitionConfiguration()** - get list of configurations for Workflow Definitions.

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
Custom data normalizer for Workflow Data Serializer, use basic serializer and collection of Attribute Normalizers.

**Methods:**
* **normalize(object, format, context)** - convert origin source data to scalar/array representation;
* **denormalize(data, class, format, context)** - convert scalar/array data to origin representation.

Attribute Normalizer
--------------------
**Interface:**
Oro\Bundle\WorkflowBundle\Serializer\Normalizer\AttributeNormalizer

**Description:**
Responsible for converting attribute values to scalar/array representation and vice versa. By default there are
two specific Attribute Normalizers: StandardAttributeNormalizer and EntityAttributeNormalizer. Any other can be
used with OroWorkflowBundle, use "oro_workflow.attribute_normalizer" tag to register your custom normalizers.

**Methods:**
* **normalize(Workflow, Attribute, attributeValue)** - convert Workflow Attribute value to scalar/array representation;
* **denormalize(Workflow, Attribute, attributeValue)** - convert Workflow Attribute value to original representation.
* **supportsNormalization(Workflow, Attribute, attributeValue)** - checks if normalization is supported
* **supportsDenormalization(Workflow, Attribute, attributeValue)** - checks if denormalization is supported

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
