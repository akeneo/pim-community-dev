/* eslint-env es6 */
const { assign } = Object
const { get, mapValues, keys } = require('lodash')
const { resolve, join } = require('path')
const { parse } = require('yamljs')
const { readFileSync, existsSync, writeFileSync } = require('fs')

const deepMerge = require('deepmerge')
const customPaths = require('./custom-paths')

const utils = {
    /**
     * Grab the RequireJS.yaml from each bundle required by the application
     * and extract the module paths, config, and maps
     *
     * @param  {Array} requireYamls An array containing the filenames of each RequireJS.yaml
     * @return {Object}             Returns an object containing the extracted config, and all the absolute module paths
     */
    getRequireConfig(requireYamls, baseDir) {
        let paths = {}
        let config = {}

        requireYamls.forEach((yaml) => {
            try {
                const contents = readFileSync(yaml, 'utf8')
                const parsed = parse(contents)
                const requirePaths = parsed.config.paths || {}
                const requireMaps = get(parsed.config, 'map.*') || {}
                const mergedPaths = assign(requirePaths, requireMaps)
                const absolutePaths = mapValues(mergedPaths, (modulePath) => {
                    return resolve(baseDir, `./web/bundles/${modulePath}`)
                })

                paths = deepMerge(paths, absolutePaths)
                config = deepMerge(config, parsed.config.config || {})
            } catch (e) {}
        })

        return { config, paths }
    },

    /**
     * Combines the absolute module paths and custom module
     * paths and allowed contexts and returns an object
     *
     * @param  {String} baseDir  The base directory where webpack is run
     * @param  {String} sourceDir The directory executing webpack
     * @return {Object}               An object requirejs paths and config, allowed context and module aliases
     */
    getModulePaths(baseDir, sourceDir) {
        const sourcePath = resolve(baseDir, 'web/js/require-paths.js')

        if (!existsSync(sourcePath)) {
            throw new Error(`The web/js/require-paths.js module does not exist - You need to run
            "bin/console pim:install" or "bin/console pim:installer:dump-require-paths" before
            running webpack \n`)
        }

        // File dumped by the pim:installer:dump-require-paths command
        const pathSourceFile = require(sourcePath)
        const { config, paths } = utils.getRequireConfig(pathSourceFile, baseDir)

        const aliases = assign(paths,
            mapValues(customPaths, custom => resolve(baseDir, custom)
        ), {
            'require-polyfill': resolve(sourceDir, './frontend/require-polyfill.js'),
            'require-context': resolve(sourceDir, './frontend/require-context.js')
        })

        utils.createModuleRegistry(keys(aliases), baseDir)

        return { paths, config, aliases }
    },

    createModuleRegistry(modules, baseDir) {
        const registryFiles = {}

        modules.forEach(file => {
            registryFiles[`'${file}'`] = `require.resolve('${file}')`
        })

        const registry = `module.exports = function(moduleName) {
            const paths = ${JSON.stringify(registryFiles).replace(/\"/g, '')};
            return __webpack_require__(paths[moduleName])
        }`

        writeFileSync(join(baseDir, './web/js/module-registry.js'), registry);
    }
}

module.exports = utils
