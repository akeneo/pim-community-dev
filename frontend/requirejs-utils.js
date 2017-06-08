/* eslint-env es6 */
require('colors')
const { assign } = Object
const { values, get, mapValues } = require('lodash')
const { resolve } = require('path')
const { parse } = require('yamljs')
const { readFileSync, existsSync } = require('fs')

const deepMerge = require('deepmerge')
const globToRegex = require('glob-to-regexp')
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
            "app/console pim:install" or "app/console pim:installer:dump-require-paths" before
            running webpack \n`.red)
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

        // Derive the allowed context paths from all the paths in all the requirejs.yml files
        const contextPaths = values(aliases).map(alias => {
          const filename = alias.replace(/.js$/, '')
          return globToRegex(filename).toString().slice(2, -2)
        })
        const context = `/^.*(${contextPaths.join('|')})$/`

        return { paths, config, context, aliases }
    }
}

module.exports = utils
