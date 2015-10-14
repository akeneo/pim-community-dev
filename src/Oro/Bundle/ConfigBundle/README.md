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
- [Config management](./Resources/doc/config_management.md)
- [Entity output config](./Resources/doc/entity_output_config.md)
- [System configuration UIX](./Resources/doc/system_configuration.md)
