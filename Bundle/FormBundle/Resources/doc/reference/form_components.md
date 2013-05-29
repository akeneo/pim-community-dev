Form Components
---------------

This article describes all form components that are stored in OroUIBundle.
Form components it is form types, data transformers and event listeners.


### Form Types

* **Form / Type / OroDateType** (name = oro_date) - encapsulates date element logic;
* **Form / Type / OroDateTimeType** (name = oro_datetime) - encapsulates datetime element logic;
* **Form / Type / EntityIdentifierType** (name = oro_entity_identifier) - converts string or array of entity IDs
to existing entities of specified type.


### Data Transformers

* **Form / DataTransformer / ArrayToStringTransformer** - converts array to string and back;
* **Form / DataTransformer / EntitiesToIdsTransformer** - converts entity IDs to entities and back.


### Event Subscribers

* **Form / EventListener / FixArrayToStringListener** - converts array to string on form PRE_BIND event.


### Configuration

#### Form Types

```
parameters:
    oro_form.type.date.class:              Oro\Bundle\FormBundle\Form\Type\OroDateType
    oro_form.type.datetime.class:          Oro\Bundle\FormBundle\Form\Type\OroDateTimeType
    oro_form.type.entity_identifier.class: Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType

services:
    oro_form.type.date:
        class: %oro_form.type.date.class%
        tags:
            - { name: form.type, alias: oro_date }

    oro_form.type.datetime:
        class: %oro_form.type.datetime.class%
        tags:
            - { name: form.type, alias: oro_datetime }

    oro_form.type.entity_identifier:
        class: %oro_form.type.entity_identifier.class%
        tags:
            - { name: form.type, alias: oro_entity_identifier }
        arguments: ["@doctrine"]
```
