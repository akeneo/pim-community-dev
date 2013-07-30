Table of Contents
-----------------
 - [What is Workflow?](#what-is-workflow)
 - [Main Entities](#main-entities)
 - [How it works?](#how-it-works)
 - [Configuration](#configuration)

What is Workflow?
=================
Workflow is a complex solution that allows user to perform set of actions with predefined conditions - each next action depends on previous. Also Workflow can be described as some kind of wizard that helps user to perform complex actions. Usually Workflow is used to manage some specific entity and to create additional related entities.

Main Entities
=============

Workflow consists of several related entities.

* **Attribute** - description of one simple data field, contains name, label, form type and options; may contain attribute entity class.
* **Step** - predefined set of fields that should be filled, contains name, label, template, list of attributes and list of allowed transactions.
* **Transaction** - action that change current step of workflow (i.e. moves from one step to another) according to specified conditions, and then performs transition post actions.
* **Condition** - defines whether specific transition is allowed with entered data; conditions can be nested.
* **Post Action** - additional action that performed after transition, can be used to create new entities.
* **Workflow Item** - specific instance of Workflow, contains all entered data in active session, saved in DB, used as main entry point for all manipulations with data.

How it works?
=============
To start workflow user have to create new Workflow Item of specific Workflow and, if required, set managed entity instance. Then user will be redirected to first step where he can enter some data and perform Transitions to other steps.

Each Step has list of allowed Transitions, and each Transition has list of Conditions that define whether this Transition can be performed with specific entered data. If Transition is allowed then user can perform it. If transition has Post Actions then these Post Actions will be performed right after transition. So, user can move through Steps of Workflow until he reach the final Step where Workflow will be finished and Workflow Item will be marked as closed.

Workflow Item stores all entered data and current step, so, user can stop Workflow at any moment and then return to it, and Workflow will have exactly the same state.

Configuration
=============

All Workflow entities except Workflow Item is described in configuration. Let's look as the example of simple Workflow configuration that creates new user.

```
create_user:                             # name of the workflow
    label: 'Create User Workflow'        # workflow label
    start_step: create_user_form         # step that will be shown first
    steps:                               # list of all existing steps in workflow
        user_form:                       # step where user should fill form with personal information
            label: 'Enter User Data'                               # step label
            template: AcmeDemoBundle:User:userForm.html.twig       # step template
            attributes:                  # list of attributes that must be rendered on form
                - username               # username field
                - age                    # age field
                - email                  # email field
            allowed_transitions:         # list of allowed transition from this step
                - create_user            # user can be created from this step
        user_summary:                    # step where user can review entered data
            label: 'User Summary'                                  # step label
            template: AcmeDemoBundle:User:userSummary.html.twig    # step template
            is_final: true                                         # this step is final
    attributes:                          # list of all existing attributes in workflow
        username:                        # username
            form_type: text              # should be shown as text field
            label: 'Username'            # field label
            options:                     # attribute options
                form_options:            # field render options
                    required: true       # field is obligatory
                    max_length: 20       # field maximum length
        age:                             # user age
            form_type: integer           # should be shown as text field with integer value
            label: 'Age'                 # field label
            options:                     # attribute options
                form_options:            # field render options
                    required: true       # field is obligatory
        email:                           # user email
            form_type: email             # should be shown as text field with email value
            label: 'Email'               # field label
            options:                     # attribute options
                form_options:            # field render options
                    required: false      # field is optional
                    max_length: 30       # field maximum length
        user:                            # user entity (not rendered, attribute is used as storage)
            form_type: entity            # entity selector
            label: 'User'                # field label
            options:                     # attribute options
                entity_class: Acme\Bundle\DemoBundle\Entity\User    # entity class name
    transitions:                             # list of all existing transitions in workflow
        create_user:                         # transition from step "user_form" to "user_summary"
            label: 'Create User'                             # transition label
            step_to: user_summary                            # next step after transition performing
            transition_definition: create_user_definition    # link to definition of conditions and post actions
    transition_definitions:                  # list of all existing transition definitions
        create_user_definition:              # definitions for transition "create_user"
            conditions:                      # required conditions: username is not empty and age >= 18
                @and:                                        # AND for all children conditions
                    - @not_empty: [$username]                # username attribute value is not empty
                    - @greater_or_equal: [$age, 18]          # age attribute value is greater or equal to 18
            post_actions:                    # required post actions: create User entity and bind it to Workflow Item
                - @create_entity:            # create entity post action
                    parameters:              # parameters of post action
                        class: Acme\Bundle\DemoBundle\Entity\User    # entity class name
                        attribute: $user                             # save entity in attribute "user"
                        data:                                # user entity data
                            username: $username              # get username value from attribute "username"
                            age: $age                        # get age value from attribute "age"
                            email: $email                    # get email value from attribute "email"
                            registered: true                 # set registered flag as true
                - @bind_entity:              # bind entity to Workflow Item post action
                    parameters:              # parameters of post action
                        attribute: $user     # get bound entity from attribute "user"
```

This configuration describes Workflow that includes two steps - "user_form" and "user_summary".

At step "user_form" user should fill small form with personal information attributes - "username" as text (required), "age" as integer (required) and "email" as email (optional).

To perform transition "create_user" several conditions must be satisfied (transition definition "create_user_definition", node "conditions"): user must enter username (condition @not_empty) and (condition @and) age must be greater of equals to 18 years (condition @greater_or_equal). If these conditions and satisfied following post actions will be performed (transition definition "create_user_definition", node "post_actions"): User entity will be created with entered data and it will be saved to attribute "user" (post action @create_entity), then this entity will be bound to Workflow Item (post action @bind_entity).

Following diagram shows this schema in graphical representation.

![Workflow Diagram](../images/getting-started_workflow-diagram.png)
