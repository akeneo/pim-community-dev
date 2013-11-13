Getting Started
===============

Table of Contents
-----------------
 - [What is Workflow?](#what-is-workflow)
 - [Main Entities](#main-entities)
 - [Entity and Wizard Workflows?](#entity-and-wizard-workflows)
 - [How it works?](#how-it-works)
 - [Managed Entities](#managed-entities)
 - [Bind Entities](#bind-entities)
 - [Configuration](#configuration)

What is Workflow?
=================

Workflow is a complex solution that allows user to perform set of actions with predefined conditions - each next action
depends on previous. Also Workflow can be described as some kind of wizard that helps user to perform complex actions.
Usually Workflow is used to manage some specific entity and to create additional related entities.

Main Entities
=============

Workflow consists of several related entities.

* **Step** - entity that shows current status of Workflow, has form options that will be used to rendered attributes on
step form as a fields and list of allowed transitions that will rendered as a buttons. Before rendering each
transitions checked is it allowed for current Workflow Item. Contains name, label and template as additional parameters.

* **Attribute** - entity that represent one value in Workflow Item, can be bind to a step form, but doesn't know about
it's form representation. Attribute knows about its type (string, object, entity etc.) and additional options to specify
whether it contains managed entity, should entity be bound to Workflow Item and can entity have several related
instances of Workflow Item. Contains name and label as additional parameters.

* **Transition** - action that change current step of Workflow Item (i.e. moves it from one step to another) according
to specified Conditions, and then performs transition Post Actions. Transition can be used as a start transition - it
means that this transition can be used to create new Workflow Item and start Workflow. Contains name, label and options
as additional parameters.

* **Init Action** - additional action that performed before Transition, can be used to manage entities (create, find),
manipulate attributes (assign values), perform any other actions.

* **Condition** - defines whether specific Transition is allowed with specified input data, conditions can be nested.

* **Post Action** - additional action that performed after Transition, can be used to manage entities (create, find),
manipulate attributes (assign values), perform any other actions.

* **Workflow Item** - entity related to specific instance of Workflow, contains all Attribute values entered by user or
automatically in active session and save it in DB, used as main entry point for all manipulations with Attributes.
One entity can have several related Workflow Items of one Workflow type. Contains Worfklow name, current Step name and
list of bound entities (these entities that must know about this Workflow Item).

Entity and Wizard Workflows
===========================

There are two types of Workflows:
* wizard
* entity (default)

When user starts wizard Workflow then he will be redirected to special Workflow page. On this page he can see next
UI blocks:
* current step label attributes form
* all other steps and their forms in read only mode
* possible transitions
* custom blocks configured by developer (for example information block with some entity data)

Unlike wizard, entity Workflow doesn't have special page and it's directly managed on entity page. Another difference
from wizard Workflow is that steps of entity Workflow cannot have forms and user performs transitions on managed entity
page by clicking on Workflow buttons.

How it works?
=============

In both cases when user clicks button with start transition in the background a new instance of Workflow Item of
specific Workflow is created and, if required, managed entity instance is set to it.

Each Step has a list of allowed Transitions, and each Transition has list of Conditions that define whether this
Transition can be performed with specific Workflow Item data. If Transition is allowed then user can perform it.
If transition has Post Actions then these Post Actions will be performed right after transition. So, user can move
through Steps of Workflow until he reach the final Step where Workflow will be finished and Workflow Item will be
marked as closed. It's also possible that Workflow doesn't have final step, in this case user can perform transitions
until they are allowed.

Workflow Item stores all collected data and current step, so, user can stop his progress on Workflow at any moment and
then return to it, and Workflow will have exactly the same state.

Managed Entities
================

Workflow can have attributes with managed entities. Values of such attributes are required. When user visits page of
some entity, this knowledge can be used to show all applicable Workflows that can be started.

When Workflow has managed entity attribute it means that the data of Workflow Item contains a reference to that entity.
If for some reason managed entity will be deleted Workflow Item won't be applicable.

Bind Entities
=============

When Workflow has attributes with bind entities this information can be used to show all bound Workflows Items on page
of the entity. Managed entities attributes are bound by default.

Configuration
=============

All Workflow entities except Workflow Item is described in configuration. Let's look as the example of simple Workflow
configuration that creates new user.

```
workflows:
    create_user:                             # name of the workflow
        label: 'Create User Workflow'        # workflow label
        start_step: create_user_form         # step that will be shown first
        enabled: true                        # is this workflow enabled, default true
        type: wizard                         # type of workflow (entity|wizard), default entity
        steps:                               # list of all existing steps in workflow
            user_form:                       # step where user should fill form with personal information
                label: 'Enter User Data'                               # step label
                template: AcmeDemoBundle:User:userForm.html.twig       # step template, default is OroWorkflowBundle:WorkflowStep:edit
                form_options:                # options of form type for rendering attributes
                    attribute_fields:
                        username:            # field for username attribute
                            form_type: text
                            options:         # options of form type
                                required: true
                                max_length: 20
                        age:                 # field for age attribute
                            form_type: integer
                            options:
                                required: true
                        email:               # field for email attribute
                            form_type: email
                            options:
                                required: false
                                label: User Email # custom label, override attribute label
                allowed_transitions:         # list of allowed transition from this step
                    - create_user            # user can be created from this step
            user_summary:                    # step where user can review entered data
                label: 'User Summary'                                  # step label
                template: AcmeDemoBundle:User:userSummary.html.twig    # custom step template
                is_final: true                                         # this step is final
        attributes:                          # list of all existing attributes in workflow
            username:                        # username attribute
                label: 'Username'            # attribute label
                type: string                 # attribute type, possible values are bool (boolean), int (integer), float, string, array, object, entity
            age:                             # age attribute
                label: 'Age'
                type: integer
            email:                           # email attribute
                type: string
                label: 'Email'
            user:                            # user entity
                type: entity
                label: 'User'
                options:                     # attribute options
                    class: Acme\Bundle\DemoBundle\Entity\User    # entity class name
                    bind: true               # make possible to find bound Workflow Items by entity
        transitions:                         # list of all existing transitions in workflow
            create_user:                     # transition from step "user_form" to "user_summary"
                label: 'Create User'                             # transition label
                step_to: user_summary                            # next step after transition performing
                transition_definition: create_user_definition    # link to definition of conditions and post actions
        transition_definitions:              # list of all existing transition definitions
            create_user_definition:          # definitions for transition "create_user"
                conditions:                  # required conditions: username is not empty and age >= 18
                    @and:                                        # AND for all children conditions
                        - @not_empty: [$username]                # username attribute value is not empty
                        - @greater_or_equal: [$age, 18]          # age attribute value is greater or equal to 18
                post_actions:                # required post actions: create User entity
                    - @create_entity:        # create entity post action
                        parameters:          # parameters of post action
                            class: Acme\Bundle\DemoBundle\Entity\User    # entity class name
                            attribute: $user                             # save entity in attribute "user"
                            data:                                # user entity data
                                username: $username              # get username value from attribute "username"
                                age: $age                        # get age value from attribute "age"
                                email: $email                    # get email value from attribute "email"
                                registered: true                 # set registered flag as true
```

This configuration describes Workflow that includes two steps - "user_form" and "user_summary".

At step "user_form" user should fill small form with personal information attributes - "username" as text (required),
"age" as integer (required) and "email" as email (optional).

To perform transition "create_user" several conditions must be satisfied (transition definition
"create_user_definition", node "conditions"): user must enter username (condition @not_empty) and (condition @and) age
must be greater of equals to 18 years (condition @greater_or_equal). If these conditions and satisfied following post
actions will be performed (transition definition "create_user_definition", node "post_actions"): User entity will be
created with entered data and it will be saved to attribute "user" (post action @create_entity).

Following diagram shows this schema in graphical representation.

![Workflow Diagram](../images/getting-started_workflow-diagram.png)
