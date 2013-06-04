## Config Bundle ##
Centralised application configuration management.

## Installation ##
Add the `oro/config-bundle` package to your `require` section in the `composer.json` file.

``` yaml
"require": {
    [...]
    "oro/config-bundle": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:laboro/ConfigBundle.git",
        "branch": "master"
    }
]
```

Add the ConfigBundle to your application's kernel:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\ConfigBundle\OroConfigBundle(),
        // ...
    );
    ...
}
```

## Run unit tests ##

``` bash
$ phpunit --coverage-html=cov/
```

## Usage ##
### Controller ###
You can access different Oro settings using different scopes.

**Note:** Currently, only `oro_config.user` scope implemented.

``` php
<?php
$config = $this->get('oro_config.user');
$value  = $config->get('oro_anybundle.anysetting');
```

To define settings inside your bundle you can use `SettingsBuilder` helper class.

YourBundle\DependencyInjection\Configuration.php:

``` php
<?php
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

// ...

public function getConfigTreeBuilder()
{
    $builder = new TreeBuilder();
    $root    = $builder
        ->root('oro_mybundle')
        ->children()
            // ...
        ->end();

     SettingsBuilder::append($root, array(
        'settingname' => array(
            'value' => true,
            'type'  => 'boolean',
        ),
        'anothersetting' => array(
            'value' => 10,
        ),
    ));

    return $builder;
}
```

`type` above could be `scalar` (which is default) or `boolean`.

### View ###

```
{% set format = oro_config_value('oro_anybundle.anysetting') %}
```

### Entity ###
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
