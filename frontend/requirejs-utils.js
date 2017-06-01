/* eslint-env es6 */
const _ = require('lodash')
const path = require('path')
const getYaml = require('yamljs')
const fs = require('fs')
const deepMerge = require('deepmerge')
const mkdirp = require('mkdirp')
const webroot = 'web/dist'

const utils = {
    /**
     * Grab the RequireJS.yaml from each bundle required by the application
     * and extract the module paths, config, and maps
     *
     * @param  {Array} requireYamls An array containing the filenames of each RequireJS.yaml
     * @return {Object}             Returns an object containing the extracted config, and all the absolute module paths
     */
    getRequireConfig(requireYamls) {
        let paths = {}
        let config = {}

        requireYamls.forEach((yaml) => {
            try {
                const contents = fs.readFileSync(yaml, 'utf8')
                const parsed = getYaml.parse(contents)
                const requirePaths = parsed.config.paths || {}
                const requireMaps = _.get(parsed.config, 'map.*') || {}
                const mergedPaths = Object.assign(requirePaths, requireMaps)
                const absolutePaths = utils.getAbsolutePaths(mergedPaths, yaml)

                paths = deepMerge(paths, absolutePaths)
                config = deepMerge(config, parsed.config.config || {})
            } catch (e) {}
        })

        return { config, paths }
    },

    /**
     * Gets the absolute path of a module and returns an object containing a map of module names to paths
     * Example: pimui/js/pim-formupdatelistener -> {user home}/src/Pim/Bundle/UIBundle/Resources/public/js/pim-formupdatelistener'
     * @param  {Object} paths    An object containing module/path mapping
     * @param  {String} yamlFilename The filename of the requirejs.yaml file
     * @return {Object}          An object containing mapping of module name to absolute file location
     */
    getAbsolutePaths(paths, yamlFilename) {
        const absolutePaths = {}

        for (let name in paths) {
            const relative = paths[name].split('/')
            relative.shift()
            const sourcePath = path.resolve(yamlFilename, '../../')
            absolutePaths[name] = `${sourcePath}/public/${relative.join('/')}`
        }

        return absolutePaths
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
    getModulePaths(pathSourceFile, overrides, baseDirectory) {
        const requireConfig = utils.getRequireConfig(pathSourceFile)

        overrides = _.mapValues(overrides,
          override => path.resolve(baseDirectory, override)
        )

        const mergedPaths = Object.assign(requireConfig.paths, overrides, {
            backbone: require.resolve('backbone'),
            routes: path.resolve('web/js/routes'),
            general: path.resolve('web/dist/general'),
            paths: path.resolve('web/dist/paths'),
            summernote: path.resolve('node_modules/summernote/dist/summernote.min.js'),
            'fos-routing-base': path.resolve('vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router'),
            CodeMirror: path.resolve('node_modules/codemirror/lib/codemirror'),
            jquery: require.resolve('jquery'),
            json: require.resolve('JSON2'),
            underscore: require.resolve('underscore')
        })

        mkdirp(webroot, function() {
            fs.writeFileSync(`${webroot}/general.js`, utils.getModuleString(requireConfig.config), 'utf8')
            fs.writeFileSync(`${webroot}/paths.js`, utils.getModuleString(mergedPaths), 'utf8')
        })

        return mergedPaths;
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
