/* eslint-env es6 */
const { values, get, mapValues } = require('lodash')
const { resolve } = require('path')
const { parse } = require('yamljs')
const { readFileSync } = require('fs')

const deepMerge = require('deepmerge')
const globToRegex = require('glob-to-regexp');
const customPaths = require('./custom-paths')

// Only modules inside one of these folders can be dynamically required
const allowedPaths = [
    'web/bundles',
    'web/bundles/pim**/*.js',
    'web/bundles/oro**/*.js',
    'web/bundles/fosjsrouting/**/*.js',
    'web/bundles/pim**/*.html',
    'frontend/**/*.js',
    'web/dist/**/*.js',
    ...values(customPaths)
]

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
                const mergedPaths = Object.assign(requirePaths, requireMaps)
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
     * Combines the absolute module paths, path overrides and custom module
     * paths - it writes them to files to be consumed by the frontend, and
     * returns the merged paths for the webpack config
     *
     * @param  {Object} overrides     A map of path overrides
     * @param  {String} baseDir  The base directory where webpack is run
     * @param  {String} sourceDir The directory executing webpack
     * @return {Object}               An object requirejs paths and config, allowed context and module aliases
     */
    getModulePaths(baseDir, sourceDir) {
        // File dumped by the pim:installer:dump-require-paths command
        const pathSourceFile = require(resolve(baseDir, 'web/js/require-paths'))
        const { config, paths } = utils.getRequireConfig(pathSourceFile, baseDir)

        const contextPaths = allowedPaths.map(glob => globToRegex(glob).toString().slice(2, -2))
        const context = `/^.*(${contextPaths.join('|')})$/`
        const aliases = Object.assign(
            paths,
            mapValues(customPaths, custom => resolve(baseDir, custom)
        ), {
            'require-polyfill': resolve(sourceDir, './frontend/require-polyfill.js'),
            'require-context': resolve(sourceDir, './frontend/require-context.js')
        })

        return { paths, config, context, aliases }
    },

    /**
     * getModuleString
     * @param  {Object} contents Some json to be stringified
     * @return {String}          Module contents
     */
    getModuleString(contents) {
        return `module.exports = ${JSON.stringify(contents)}`
    }
}

module.exports = utils
