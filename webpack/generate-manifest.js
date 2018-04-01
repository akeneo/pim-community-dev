const process = require('process');
const requirePaths = require('../web/js/require-paths');
const merge = require('deepmerge');
const rootDir = process.cwd();
const formExtensions = require('./form-extensions.js');
const extensionPaths = formExtensions.collectPaths();

const { parse } = require('yamljs');
const { readFileSync, writeFileSync } = require('fs');
const { resolve, join } = require('path');

const requireContents = merge.all(requirePaths.map(requireJsFile => {
    let contents = '';

    try {
        contents = readFileSync(requireJsFile, 'utf8');
    } catch (e) {}

    return (parse(contents) || {}).config;
}).filter(paths => undefined !== paths));


/**
 * This method takes every requirejs.yml from the app bundles and
 * returns an object that contains a mapping of every module to alias.
 *
 * For example:
 * {
 *     'pim/form/product': './src/Bundle/EnrichBundle/Resources/public/js/form/product.js'
 *     ...
 * }
 *
 * Webpack will use this file to resolve the modules in the 'define' block
 *
 * For example:
 *
 * define(['pim/form/product'], (ProductForm) =>{ ProductForm.foo() })
 */
function getModuleAliases() {
    let aliases = {};

    for (let alias in requireContents.paths) {
        const path = `./web/bundles/${requireContents.paths[alias]}`;
        aliases[alias] = resolve(rootDir, path);
    }

    aliases = Object.assign(aliases, {
        'require-polyfill': resolve(__dirname, './require-polyfill.js'),
        'require-context': resolve(__dirname, './require-context.js'),
        'module-registry': resolve(rootDir, './web/js/module-registry.js'),
        routes: resolve(rootDir, './web/js/routes.js'),
        'fos-routing-base': resolve(rootDir, './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js'),
        summernote: resolve(rootDir, 'node_modules/summernote/dist/summernote.js'),
        json: resolve(rootDir, 'node_modules/JSON2/json2.js'),
        jquery: require.resolve('jquery'),
        underscore: require.resolve('underscore')
    });

    return aliases;
}

/**
 * This function gets the merged config from every requirejs.yml file in a registered bundle.
 * It also injects the form extensions config into the form config provider.
 */
function getModuleConfigs() {
    const extensions = formExtensions.merge(extensionPaths);
    requireContents.config['pim/form-config-provider'] = extensions;

    return requireContents.config;
}

/**
 * Gathers a list of all the modules required in the app and dumps a module registry
 *
 * This is used when we have dynamic requires, since the twig templates still contain js modules in them.
 *
 * @param {Object} aliases
 */
function dumpModuleRegistry(aliases) {
    const registryFiles = {};

    aliases.forEach(file => {
        registryFiles[`'${file}'`] = `require.resolve('${file}')`;
    });

    const registry = `module.exports = function(moduleName) {
        const paths = ${JSON.stringify(registryFiles).replace(/\"/g, '')};

        if (paths[moduleName] === undefined) {
            return console.error(moduleName + ' is missing from the registry - include it in your requirejs.yml and clear the app cache');
        }

        return __webpack_require__(paths[moduleName])
    }`;

    writeFileSync(join(rootDir, './web/js/module-registry.js'), registry);
}

const moduleAliases = getModuleAliases();

const manifest = {
    rootDir,
    moduleAliases,
    extensionPaths,
    moduleConfigs: getModuleConfigs()
};

dumpModuleRegistry(Object.keys(moduleAliases));
writeFileSync('./webpack/manifest.json', JSON.stringify(manifest));
