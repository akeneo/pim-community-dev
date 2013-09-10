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
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

/**
 * @ORM\Entity()
 * @ORM\Table(name="my_table")
 * @Oro\Loggable
 */
class MyEntity
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Oro\Versioned
     */
    protected $myField;

    /**
     * @var MyCollectionItem[]
     *
     * @ORM\ManyToMany(targetEntity="MyCollectionItem")
     * @Oro\Versioned("getLabel") // "getLabel" it is a method which provides data for Loggable. If method doesn't set Loggable will use "__toString" for relation entity.
     */
    protected $myCollection;
}

That's it! `myField` and `$myCollection` becomes versioned and will be tracked by DataAudit bundle.
