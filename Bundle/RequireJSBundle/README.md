OroRequireJSBundle
====================
This bundle provides easy way to:

 -  generates require.js config file for a project;
 -  optimizes, minify and merge all JS-file into one resources.

For details of configuration options see [RequireJS API].<br />
For details of build options see [example.build.js].

## Require.js config generation
### Configuration
Common options for require.js config are placed in ```app/config.yml```:

    oro_require_js:
        config:                                 # common options which will eventually get into require.js config file
            waitSeconds: 0
            enforceDefine: true
            scriptType: 'text/javascript'

Bundle specific options are defined inside ```requirejs.yml``` file, which is placed in ```%BundleName%\Resources\config\requirejs.yml```.
It can have three sections ```shim```, ```map``` and ```paths``` (see [RequireJS API]).
Each bundle's javascript module have to be defined in ```paths``` section, where key is a module name and value is its relative path from document root.

    config:
        shim:
            'jquery-ui':
                deps:
                    - 'jquery'
        map:
            '*':
                'jquery': 'oro/jquery-setup'
            'oro/jquery-setup':
                'jquery': 'jquery'
        paths:
            'jquery': 'bundles/oroui/lib/jquery-1.10.2.js'
            'jquery-ui': 'bundles/oroui/lib/jquery-ui.min.js'
            'oro/jquery-setup': 'bundles/oroui/js/jquery-setup.js'

### Generation
Main require.js config is generated automatically and embedded in HTML-page. The config is stored in application cache. So if you want, for some reason, renew a require.js configuration, then just clean a cache.

## Runtime main require.js config extension

Sometimes it is necessary to modify require.js configuration on a fly (e.g. to set ```baseUrl``` for all JS-resources or define module with dynamic URL).
It is possible to do over Twig extension ```placeholder``` (provided by OroUIBundle).
In ```%BundleName%\Resources\config\placeholders.yml``` define a twig template for placeholder ```requirejs_config_extend```:

    placeholders:
        requirejs_config_extend:
            items:
                ui_requirejs_config:
                    order: 100

    items:
        ui_requirejs_config:
            template: OroUIBundle::requirejs.config.js.twig

In that template you can write piece of custom configuration which will be applied after general configuration is loaded and before any module get utilized.
The template produces JS-code which is actually ```require();``` function call with a single argument - JS-object with a piece of configuration.

E.g. dynamically defines ```baseUrl``` option, depending on what the original request was (OroUIBundle):

    require({
        baseUrl: '{{ asset('bundles') }}'
    });


Or dynamically defines path to translations dictionary (depending on what locale is currently used) and prevents any translations calls before the dictionary is loaded (OroTranslationBundle):

    require({
        shim: {
            'oro/translations': {
                deps: ['oro/translator', 'translator'],
                init: function(__) {
                    return __;
                }
            }
        },
        map: {
            '*': {
                'oro/translator': 'oro/translations'
            },
            'oro/translations': {
                'oro/translator': 'oro/translator'
            }
        },
        paths: {
            'oro/translations': '{{ url('oro_translation_jstranslation')[0:-3] }}'
        }
    });

In terms of sequence of code execution it looks:

 1. Prod mode (and built resource exists)
    - execute all custom configurations<br />
    ```require(/* ... */); require(/* ... */); require(/* ... */);```
    - load single minified js-resource (with ```require-config.js``` + ```require.js``` and rest of modules)
 1. Dev mode (or built resource does not exist)
    - load ```js/require-config.js```
    - load ```require.js```
    - execute all custom configurations<br />
    ```require(/* ... */); require(/* ... */); require(/* ... */);```

See ```@OroRequireJSBundle::scripts.html.twig```

## Build project
### Configuration
Build configuration starts in ```app/config.yml```

    oro_require_js:
        build_path: "js/oro.min.js"     # relative path from document root folder to project built
        building_timeout: 3600
        js_engine: "node"               # can be configured to use other engine, e.g. Rhino
        build:                          # build.js's common options
            optimize: "uglify2"
            preserveLicenseComments: true
            generateSourceMaps: true
            useSourceUrl: true

See details for [```oro_require_js.build```][example.build.js] options.

Beside general build-configuration, you can set bundle specific options inside ```%BundleName%\Resources\config\requirejs.yml``` file, root section ```build```.

    build:
        paths:
            'autobahn': 'empty:'

This directive will prevent module from getting concatenated into build file.

### Building
To make a build for JS-resources, just execute a command in console:

    php app/console oro:requirejs:build

It will:

1. take initial configuration from ```oro_require_js.build``` (```app/config.yml```);
1. extend it with configuration found in bundles (```%BundleName%\Resources\config\requirejs.yml```);
1. generate ```build.js``` - a config for builder;
1. run builder (time consuming process, especially for Rhino JS-engine);
1. remove ```build.js```.

[RequireJS API]: <http://requirejs.org/docs/api.html#config>
[example.build.js]: <https://github.com/jrburke/r.js/blob/master/build/example.build.js>
