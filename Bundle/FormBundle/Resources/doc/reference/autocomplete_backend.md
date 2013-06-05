Autocomplete Backend
----------------------

#### Overview

Autocomplete consists of next components:

* Form Type Configuration
* Autocomplete Configuration
* Controller
* Search Handler
* Search Handler Factory

#### Form Type Configuration

Field with autocomplete behavior can be added using form type ["oro_jqueryselect2_hidden"](./autocomplete_form_type.md).
Each field must be configured with name of autocomplete configuration.

#### Autocomplete Configuration

Configuration can be stored in *Resources/config/autocomplete.yml* files of any bundle that require some custom
autocomplete fields.

*Configuration Format*


```yml
autocomplete_entities: # Root config element
    users: # Unique name of autocomplete configuration
        type: doctrine_entity # Required, type of service which handles search requests
        entity_class: Foo\BarBundle\Entity\User
        property: firstname
```


#### Controller

#### Search Handler

#### Search Handler Factory
