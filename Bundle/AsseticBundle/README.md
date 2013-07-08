OroAsseticBundle
========================

To implement hashtag navigation we must all basic javascript and css files was loaded in main template.
To do this, each bundle can has config file assets.yml with the list of js and css files.

```yaml
js:
  'some_group'
    - '@Path/To/Js/first.js'
    - '@Path/To/Js/second.js'
    - '@Path/To/Js/third.js'
css:
  'css_group':
      - '@Path/To/Css/first.css'
      - '@Path/To/Css/second.css'
      - '@Path/To/Css/third.css'
```

Js and css sections contain group of file. This groups can be uncompress to debug.

To turn off group 'css_group' for css files, the next configuration mut be added in config.yml file:

```yaml
oro_assetic:
    uncompress_js: ~
    uncompress_css: [css_group]
```

In main template must be added the next tags:

```
    {% oro_js filter='array with filters' output='js/name_of_output_file.js' %}
        <script type="text/javascript" src="{{ asset_url }}"></script>
    {% endoro_js %}
    {% oro_css filter='array with filters' output='css/name_of_output_file.css' %}
        <link rel="stylesheet" media="all" href="{{ asset_url }}" />
    {% endoro_css %}
```
This tags is the same as assettics "javascripts" and "stylesheets" tags but without list of files.

To compile blocks of files into single file, there is command

```
php app/console oro:assetic:dump
```

To use uncompressed files, in routing.yml must be added the next route rule:

```yml
oro_assets:
    resource: .
    type: oro_assetic
```