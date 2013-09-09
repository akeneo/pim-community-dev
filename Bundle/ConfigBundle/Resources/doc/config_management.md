## Config management ##
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
