Autocomplete Form Type
----------------------

#### Overview

Autocomplete element is based on [GenemuFormBundle](https://github.com/genemu/GenemuFormBundle) [Select2](http://ivaynberg.github.io/select2/)
form type. In case when autocomplete functionality is required for static selects
or for entity based selects generic genemu_jqueryselect2_* form types may be used. For example:

- genemu_jqueryselect2_choice
- genemu_jqueryselect2_country
- genemu_jqueryselect2_entity

oro_jqueryselect2_hidden was created to add more complex support of AJAX based data sources.
Main differences from genemu_jqueryselect2_hidden are:

- support of configuration based autocompletition
- selected value text is shown on entity edit form
- pre-configured ability to work with doctrine entities, flexible entities and grids

#### Form Type Configuration

Consider there is a form type that should have a field with support of autocomplete powered by Select2 jQuery plugin:

```php
class ProductType extends AbstractType
{
/**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'user',
            'oro_jqueryselect2_hidden',
            array(
                // Required values
                'autocomplete_alias' => 'users',

                // Default values
                'configs' => array(
                    'extra_config'            => 'autocomplete',
                    'selection_template_twig' => null,
                    'result_template_twig'    => null,
                    'placeholder'             => 'Choose a value...',
                    'allowClear'              => true,
                    'minimumInputLength'      => 1,
                    'ajax'                    => array()
                ),
                'search_handler' => null,
                'route_name'     => 'oro_form_autocomplete_search',
                'url'            => null
            )
        );
    }

    // ...
}
```
Default options can be ommited as they will have default values.

**autocomplete_alias**

This option refers to a service configured with tag "oro_form.autocomplete.search_handler". Details of service configuration
described [here](#search-handler-configuration)

**configs.extra_config**

This option changes the block name in twig template that will be used to add extra configuration to select2 jQuery plugin.
Make sure that block with name "oro_combobox_dataconfig_%extra_config%" exists. There are two predefined values that can be used:
"autocomplete" (block name "oro_combobox_dataconfig_autocomplete") and "grid" (block name "oro_combobox_dataconfig_grid").

If you need to extend select2 logic you can add a block in twig template with name of your "extra_config" and do all customization there.

**configs.selection_template_twig**

A name of Twig template that contain [underscore.js](http://underscorejs.org/) template.
This template will be used in dropdown list to render each result row.
Example of template:
```
<%= highlight(firstName) %> <%= highlight(lastName) %> (<%= highlight(email) %>) %>)
```

**configs.result_template_twig**

Difference from "selection_template_twig" is that it will be used to render value when it is selected.

**configs.placeholder**

A string that will be displayed when field doesn't have a value.

**configs.allowClear**

Controls possibility to make selected value empty.

**configs.minimumInputLength**

Count of characters that should be typed before request to remote server will be send.

**configs.ajax**

Custom options that are used by select2 jQuery plugin.

**configs.search_handler**

You can provide your instance of search handler (instance of Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface) in this option.
Otherwise it will be resolved using "autocomplete_alias" option.

**route_name**

Url of this route will be used by select2 plugin to iteract with search handler.
By default  Oro\Bundle\FormBundle\Controller\AutocompleteController::searchAction is used
but you can implement your own action and use it by referencing it via *route_name*.

**url**

If your search service is located remotely on other server you can pass direct url that will be used instead of *route_name*.

#### Search Handler Service

This service has several responsibilities:
* searching results that matches queries when user types characters in field on the web page
* converting each found entities to associated array that will be used on side of view and particularly in js code that
  renders search results
* providing information about entity class name that is handled, this information is used in form type to transform
  id to entity object using transformer

Generic way to declare a search handler service and make possible to reference it using option "autocomplete_alias" is
to add declaration like below:

```yml
services:
    users_search_handler:
        parent: oro_form.autocomplete.search_handler
        arguments:
            - %user_class% # pass class name of entity
            - ["firstName", "lastName"] # pass properties that should be transported to the client
        tags:
            - { name: oro_form.autocomplete.search_handler, alias: users }

```

After this "oro_jqueryselect2_hidden" form type can receive option "autocomplete_alias" with value "users".

This services receives a class name of entity that will be used by form type and during search requests. Also it
receives properties names that control what data will be transported to select2 javascript widget.

This services can be parent of abstract service "oro_form.autocomplete.search_handler" but if you need your
own implementation of search handler you should implement Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface.


#### Iteraction of Server and Javascript

Server action receives next parameters from client:
* **name** - alias of search handler that is specified using tag "oro_form.autocomplete.search_handler"
* **query** - search string
* **page** - number of page to return
* **per_page** - how many records service should return

Select2 plugin on client side expects response in next format:
```
{
    "results": [{"id": 1, "firstName": "John", "lastName": "Doe"}, {...}, ...]
    "more": true|false
}
```

Properties "firstName" and "lastName" are configured in search handler service.


#### Dependecny on OroSearchBundle

Default implementation of search handler is based on functionality of OroSearchBundle. If you use this implementation
your entity should be properly configured in the way that OroSearchBundle allows.
