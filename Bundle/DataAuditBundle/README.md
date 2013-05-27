Data Audit Bundle
=================
Bundle provides entity change log functionality using "Loggable" Doctrine extension.

## Installation ##
Add the `oro/data-audit-bundle` package to your `require` section in the `composer.json` file.

``` yaml
"require": {
    [...]
    "oro/data-audit-bundle": "dev-master"
},
"repositories": [
    [...]
    {
        "type": "vcs",
        "url": "git@github.com:laboro/DataAuditBundle.git",
        "branch": "master"
    }
]
```

Add the DataAuditBundle to your application's kernel:

``` php
<?php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Oro\Bundle\DataAuditBundle\OroDataAuditBundle(),
        // ...
    );
    // ...
}
```

Enable Loggable behavior in your `app/config/config.yml` file and customize `LoggableListener`:

``` yaml
stof_doctrine_extensions:
    orm:
        default:
            [...]
            loggable: true
    class:
        [...]
        loggable: Oro\Bundle\DataAuditBundle\EventListener\LoggableListener
```

To enable log view and API calls, import routing rules in `app/config/routing.yml`

``` yaml
oro_dataaudit:
    resource: "@OroDataAuditBundle/Resources/config/routing.yml"
```

## Run unit tests ##

``` bash
$ phpunit --coverage-html=cov/
```

## Usage ##
In your entity add special annotations to mark particular fields versioned.

``` php
<?php
// ...
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="my_table")
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class MyEntity
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Gedmo\Versioned
     */
    protected $myField;

    // ...
}

That's it! `myField` becomes versioned and will be tracked by DataAudit bundle.

You may also specify your own logEntryClass with custom logic.