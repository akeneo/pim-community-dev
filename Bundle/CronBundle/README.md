Oro Cron Bundle
===============
Centralised cron management.

## Installation ##
Add the `oro/cron-bundle` package to your `require` section in the `composer.json` file.

``` yaml
"require": {
    [...]
    "oro/cron-bundle": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:laboro/CronBundle.git",
        "branch": "master"
    }
]
```

Add the CronBundle to your application's kernel:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\CronBundle\OroCronBundle(),
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
All you need is to add `oro:cron` command to a system cron (on *nix systems), for example:

``` bash
*/1 * * * * /usr/local/bin/php /path/to/app/console --env=prod oro:cron >> /dev/null
```

On Windows you can use Task Scheduler from Control Panel.