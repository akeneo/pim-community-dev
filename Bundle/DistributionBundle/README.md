Oro Distribution Bundle
=======================

## Installation ##
Add the `oro/distribution-bundle` package to your `require` section in the `composer.json` file.

``` yaml
"require": {
    [...]
    "oro/distribution-bundle": "dev-master"
},
"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:laboro/DistributionBundle.git",
        "branch": "master"
    }
]
```

## Usage ##
Add Resources/config/oro/bundles.yml file to every bundle you want to be autoregistered:

``` yml
bundles:
    - VendorName\Bundle\VendorBundle\VendorAnyBundle
    - My\Bundle\MyBundle\MyCustomBundle
#   - ...
```

That's it! Your bundle (and "VendorAnyBundle") will be automatically registered in AppKernel.php.

## Routing autoload ##
Add Resources/config/oro/routing.yml file to every bundle for which you want to autoload its routes.

Add following rule to application's `routing.yml`:

``` yml
oro_auto_routing:
    resource: .
    type:     oro_auto
```

All routes from your bundles will be imported automatically.
