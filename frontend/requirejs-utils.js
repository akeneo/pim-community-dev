/* eslint-env es6 */
const _ = require('lodash')
const { resolve } = require('path')
const getYaml = require('yamljs')
const fs = require('fs')
const deepMerge = require('deepmerge')
const path = require('path')
const globToRegex = require('glob-to-regexp');
const customPaths = require('./custom-paths')
const allowedPaths = [
    'web/bundles',
    'web/bundles/pim**/*.js',
    'web/bundles/oro**/*.js',
    'web/bundles/fosjsrouting/**/*.js',
    'web/bundles/pim**/*.html',
    'frontend/**/*.js',
    'web/dist/**/*.js',
    ..._.values(customPaths)
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
                const contents = fs.readFileSync(yaml, 'utf8')
                const parsed = getYaml.parse(contents)
                const requirePaths = parsed.config.paths || {}
                const requireMaps = _.get(parsed.config, 'map.*') || {}
                const mergedPaths = Object.assign(requirePaths, requireMaps)
                const absolutePaths = _.mapValues(mergedPaths, (modulePath) => {
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
     * @param  {String} pathSourceFile    Absolute file path of the file dumped by the pim:installer:dump-require-paths command
     * @param  {Object} overrides     A map of path overrides
     * @param  {String} baseDirectory The base directory where webpack is run
     * @return {Object}               An object containing module name to path mapping
     */
    getModulePaths(pathSourceFile, baseDir, sourceDir) {
        const { config, paths } = utils.getRequireConfig(pathSourceFile, baseDir)

        const contextPaths = allowedPaths.map(glob => globToRegex(glob).toString().slice(2, -2))
        const context = `/^.*(${contextPaths.join('|')})$/`
        const aliases = Object.assign(
            paths,
            _.mapValues(customPaths, custom => path.resolve(baseDir, custom)
        ), {
            'require-polyfill': path.resolve(sourceDir, './frontend/require-polyfill.js'),
            'require-context': path.resolve(sourceDir, './frontend/require-context.js')
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
    },

    getAllowedContexts() {

    },


}

module.exports = utils
