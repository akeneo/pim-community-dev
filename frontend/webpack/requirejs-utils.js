const _ = require('lodash');
const deepMerge = require('deepmerge');
const path = require('path');
const { parse } = require('yamljs');
const {
    readFileSync,
    writeFileSync,
    readdirSync,
    statSync
} = require('fs');


const getFrontModules = (sourceDir, originalDir, bundle) => (dir, modules) => {
    dir = dir || originalDir + '/';
    modules = modules || {};
    const files = readdirSync(dir);

    files.forEach(function(file) {
        if (statSync(dir + file).isDirectory()) {
            modules = getFrontModules(sourceDir, originalDir)(dir + file + '/', modules);
        } else {
            const filePath = (dir + file).substring(originalDir.length);
            const fileInfo = path.parse(filePath);
            if (['.ts', '.tsx'].includes(fileInfo.ext)) {
                modules[`${fileInfo.dir.substring(1)}/${fileInfo.name}`] =
                    `${sourceDir}/${originalDir.substring(2)}${fileInfo.dir}/${fileInfo.base.replace(fileInfo.ext, '')}`;
            }
        }
    });

    return modules;
}

const utils = {
    /**
     * Grab the RequireJS.yaml from each bundle required by the application
     * and extract the module paths, config, and maps
     *
     * @param  {Array} bundlePaths An array containing the paths of each required bundle
     * @return {Object}             Returns an object containing the extracted config, and all the absolute module paths
     */
    getRequireConfig(bundlePaths, baseDir) {
        let paths = {};
        let config = {};

        bundlePaths.forEach((bundle) => {
            try {
                const contents = readFileSync(`${bundle}/Resources/config/requirejs.yml`, 'utf8');
                const parsed = parse(contents);
                const requirePaths = parsed.config.paths || {};
                const requireMaps = _.get(parsed.config, 'map.*') || {};
                const mergedPaths = Object.assign(requirePaths, requireMaps);
                const absolutePaths = _.mapValues(mergedPaths, (modulePath) => {
                    return path.resolve(baseDir, `./public/bundles/${modulePath}`);
                });

                paths = deepMerge(paths, absolutePaths);
                config = deepMerge(config, parsed.config.config || {});
            } catch (e) {}
        });

        return { config, paths };
    },

    /**
     * Combines the app module paths with external dependency paths and returns an object
     *
     * @param  {String} baseDir  The base directory where webpack is run
     * @param  {String} sourceDir The directory executing webpack
     * @return {Object}               An object requirejs containing module config and aliases
     */
    getModulePaths(baseDir, sourceDir) {
        const pathSourceFile = require(path.join(baseDir, 'public/js/require-paths.js'));
        const { config, paths } = utils.getRequireConfig(pathSourceFile, baseDir);
        const aliases = Object.assign(paths, getFrontModules(process.cwd(), './public/bundles')(), {
          'require-polyfill': path.resolve(sourceDir, './frontend/webpack/require-polyfill.js'),
          'require-context': path.resolve(sourceDir, './frontend/webpack/require-context.js'),
          'module-registry': path.resolve(baseDir, './public/js/module-registry.js'),
          routes: path.resolve(baseDir, './public/js/routes.js'),
          'fos-routing-base': path.resolve(
            baseDir,
            './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js'
          ),
          summernote: path.resolve(baseDir, 'node_modules/summernote/dist/summernote.js'),
          jquery: require.resolve('jquery'),
          underscore: require.resolve('underscore'),
          'pim/dropzonejs': require.resolve('dropzone'),
          '@akeneo-pim-community/connectivity-connection': path.resolve(
            baseDir,
            'public/bundles/akeneoconnectivityconnection-react/index.ts'
          ),
        });

        return { config, aliases };
    },

    /**
     * Generates a module containing a map registry of all modules used in the app
     * This file is consumed by require-context to 'dynamically' fetch modules
     *
     * @param  {Array} modules An array of module names e.g. ['pim/app', 'pim/templates/attribute']
     * @param  {String} baseDir The directory of the repo executing the command
     */
    createModuleRegistry(modules, baseDir) {
        const registryFiles = {};

        modules.forEach(file => {
            registryFiles[`'${file}'`] = `require.resolve('${file}')`;
        });

        const registry = `module.exports = function(moduleName) {
            const paths = ${JSON.stringify(registryFiles).replace(/\"/g, '')};

            if (paths[moduleName] === undefined) {
                return console.error(moduleName + ' is missing from the registry - include it in your requirejs.yml and clear the app cache');
            }

            return __webpack_require__(paths[moduleName])
        }`;

        writeFileSync(path.join(baseDir, './public/js/module-registry.js'), registry);
    }
};

module.exports = utils;
