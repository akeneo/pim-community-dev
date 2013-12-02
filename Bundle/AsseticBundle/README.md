OroAsseticBundle
========================

OroAssetic enables expandable and optimized way to manage CSS assets that are distributed across many bundles.
With OroAssetic developer can define CSS and JavaScript files groups in assets.yml configuration of the bundle. Defined
files will be automatically merged and optimized for web presentation. For development and debug purposes some files can
be excluded from optimization process.


Example of assets.yml file:
```yaml
css:
    'css_group':
        - 'Assets/Path/To/Css/first.css'
        - 'Assets/Path/To/Css/second.css'
        - 'Assets/Path/To/Css/third.css'
```

Css section contain groups of files. This groups can be excluded from optimization process debugging purposes.

The path to file can be defined as @BundleName/Resources/puclic/path/to/file.ext or bundles/bundle/path/to/file.ext.
If the file path contains @, then in debug mode it will be taken via controller. If path doesn't contain @, then file
will be taken via request to web folder.

For example, to turn off compression of css files in 'css_group' group the following configuration should be added
to app/config/config.yml (or app/config/config_{mode}.yml) file:

```yaml
oro_assetic:
    css_debug: [css_group]
```
In order to enable debug mode for all CSS files following configuration can be applied:

```yaml
oro_assetic:
    css_debug_all: true
```

Cache cleanup and Oro assetics dump required after:

```php
php app/console cache:clear
php app/console oro:assetic:dump
```


To get list of all available asset groups next command should be used:

```php
php app/console oro:assetic:dump show-groups
```

The next code must be added in main template:

```
    {% oro_css filter='array with filters' output='css/name_of_output_file.css' %}
        <link rel="stylesheet" media="all" href="{{ asset_url }}" />
    {% endoro_css %}
```
These tags are the same as assetics "stylesheet" tags but without list of files.

To compile blocks of files into single file, there is command

```
php app/console oro:assetic:dump
```
