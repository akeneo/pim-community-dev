## Entity Config ##
It is possible to configure entity output through the yaml config file named `entity_output.yml`. Sample syntax:

``` yaml
Acme\Bundle\MyBundle\Entity\MyEntity:
    icon_class:                         icon-entity
    name:                               entity.myentity.name
    description:                        entity.myentity.description
```

To make name and description translatable, add `translations/config.{locale}.yml` file:

``` yaml
entity:
    myentity:
        name:           My entity
        description:    My entity description
```

Then, you can use customized entity parameters in views:

```
{{ oro_config_entity('Acme\\Bundle\\MyBundle\\Entity\\MyEntity').name|trans({}, 'config') }}
```
