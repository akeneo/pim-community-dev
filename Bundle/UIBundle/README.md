Platform User Interface
=======================
User interface layouts and controls.

## Table of Contents

- [Form Components](./Resources/doc/reference/form_components.md)
- [JavaScript Tools and Libraries](./Resources/doc/reference/js_tools_and_libraries.md)

## Configuration Settings

- oro_ui.application_name - application name to display in header
- oro_ui.application_title - application title for name reference in header

## Introduction to placeholders

In order to improve layouts and make them more flexible a new twig token 'placeholder' is implemented. It allows us to combine
several blocks (templates or actions) and output them in different places in twig templates. This way we can customize layouts
without modifying twig templates.

### Placeholder declaration in YAML

Placeholders can be defined in any bundle under /SomeBundleName/Resource/placeholders.yml

```yaml
items:                             # items to use in placeholders (templates or actions)
 <item_name>:                      # any unique identifier
    template: <template>           # path to custom template for renderer
 <another_item_name>:
    action: <action>               # action name (e.g. OroSearchBundle:Search:searchBar)

placeholders:
  <placeholder_name>:
    label: <placeholder_label>
    items:
      <item_name>:
        order: 100                 # sort order in placeholder
      <another_item_name>:
        order: 200
      <one_more_item_name>: ~      # sort order will be set to 1
```

Any configuration defined in bundle placeholders.yml file can be overridden in app/config/config.yml file.

```yaml
oro_ui:
    placeholder_items:
        <placeholder_name>:
            items:
                <item_name>:
                    remove: true   # remove item from placeholder
        <another_placeholder_name>:
            items:
                <item_name>:
                    order: 200     # change item order in placeholder
```

### Rendering placeholders

To render placeholder content in twig template we need to put

```html
{% placeholder <placeholder_name> %}
```

Additional options can be passed to all placeholder child items using 'with' e.g.

```html
{% placeholder <placeholder_name> with {'form' : form} %}
```

## Templates Hinting

UIBundle allows to enable templates hinting and in such a way helps to frontend developer to find proper template.
This option can be enabled in application configuration with redefining base template class for twig:

 ```yaml
 twig:
     base_template_class: Oro\Bundle\UIBundle\Twig\Template
 ```

As e result of such change user can find HTML comments on the page
```html
<!-- Start Template: BundleName:template_name.html.twig -->
...
<!-- End Template: BundleName:template_name.html.twig -->
```
or see "template_name" variable for AJAX requests that expecting JSON
```json
"template_name":"BundleName:template_name.html.twig"
```

Templates hinting is enabled by default in development mode.