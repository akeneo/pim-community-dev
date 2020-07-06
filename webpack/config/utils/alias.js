const deepmerge = require('deepmerge');
const path = require('path');
const yamljs = require('yamljs');
const fs = require('fs');
const colors = require('colors/safe');

function findRequirejsConfigsFromSymfonyBundles(bundlesPaths) {
    console.log('Finding', colors.yellow('modules'), 'from Symfony Bundles "requirejs.yml" config files:');

    return bundlesPaths.reduce(
        (results, bundlePath) => {
            try {
                const requirejsPath = path.join(bundlePath, 'Resources/config/requirejs.yml');
                if (false === fs.existsSync(requirejsPath)) {
                    console.log(colors.grey(`- ${bundlePath}`));

                    return results;
                }

                const contents = fs.readFileSync(requirejsPath, 'utf8');
                const {paths, config} = yamljs.parse(contents).config;

                console.log(colors.green(`+ ${bundlePath}`), Object.keys(paths).length);

                results.config = deepmerge(results.config, config || {});
                results.aliases = deepmerge(results.aliases, paths);

                return results;
            } catch (e) {
                console.error(e);
            }
        },
        {config: {}, aliases: {}}
    );
}

function createModuleRegistry(modules, baseDir) {
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

    try {
        fs.writeFileSync(path.join(baseDir, './public/js/module-registry.js'), registry);
    } catch (e) {
        console.error(e);
    }

    console.log('Created "module-registry.js" with', modules.length, 'modules');
}

module.exports = {createModuleRegistry, findRequirejsConfigsFromSymfonyBundles};
