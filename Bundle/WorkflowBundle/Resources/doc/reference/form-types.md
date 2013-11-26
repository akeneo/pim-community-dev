Getting Started
===============

Table of Contents
-----------------
 - [Attributes Type](#attributes-type)
 - [Workflow Step Type](#workflow-step-type)
 - [Workflow Transition Type](#worfklow-transition-type)

Attributes Type
========================

Abstract form type used to work with workflow attributes. Attribute form type automatically adds forms
for all input attributes. Form type receives all options from workflow configuration section
"form_options" that can be specified for step and for transition.

**Example of form options configuration:**

```
form options:
    attribute_fields:
        opportunity_name:
            form_type: text
            options:
                required: true
                constraints:
                  - NotBlank: ~
        account:
            form_type: orocrm_account_select
            options:
                required: false
        company_name:
            form_type: text
            options:
                required: false
```

To avoid usage and validation of all WorkflowData attribute values form type uses event listeners for the following
events:
 - **PRE_SET_DATA** - extracts from WorkflowData only attributes specified in list of current fields, creates
new instance of WorkflowData and replaces source entity with new created one;
 - **SUBMIT** - performing opposite action: receives data from form and merges it to existing WorkflowData entity.

**Additional form options:**

 - **attribute_fields** - required, list of attributes form types options;
 - **workflow_item** - optional, instance of WorkflowItem entity;
 - **workflow** - optional, instance of Workflow;
 - **workflow_name** - optional, name of Workflow;
 - **disable_attribute_fields** - optional, a flag to disable all attributes fields.

Main form option is "attribute_fields" that contains list of attribute fields that must be rendered on form.
Each attribute field contain form type name and array of options, and attributes type adds a new form
with specified form type and options for each attribute. Also form type automatically adds missing form labels
based on attribute labels.

Attribute form type has option "disable_attribute_fields" that provides ability to disable all form fields at once -
for example, this functionality is used to disable step forms for not current steps. Default value for this option
is "false".

Also there are three additional optional options "workflow_item", "workflow" and "workflow_name" that are providing
appropriate workflow information - these data can be used in descendant classes.
