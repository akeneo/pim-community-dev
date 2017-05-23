/* eslint-env es6 */
const path = require('path')
const yaml = require('yamljs')
const fs = require('fs')
var deepMerge = require('deepmerge')
const _ = require('lodash')
const mkdirp = require('mkdirp')

const requireUtils = {
    exportModule(dest, contents) {
        const fileName = `web/dist/${dest}`
        console.log(`    Exporting module to dist - ${fileName}`.grey)
        fs.writeFileSync(fileName, `module.exports = ${contents}`, 'utf8')
    },
    getRequireConfig(requireJSFiles) {
        let modulePaths = {}
        let config = {}

        console.log('→ Grabbing requirejs.yml config from bundles'.green)

        requireJSFiles.forEach((file) => {
            try {
                console.log(`    → ${file}`.grey)
                const contents = fs.readFileSync(file, 'utf8')
                const parsedFile = yaml.parse(contents)
                const bundleConfig = parsedFile.config.config || {}
                const absolutePaths = requireUtils.getAbsolutePaths(
                    Object.assign(
                        parsedFile.config.paths || {},
                        _.get(parsedFile.config, 'map.*')
                    ), file
                )

                modulePaths = deepMerge(modulePaths, absolutePaths)
                config = deepMerge(config, bundleConfig)
            } catch (e) {}
        })

        return { config, modulePaths }
    },
    getAbsolutePaths(paths, sourceConfig) {
        const absolutePaths = {}

        for (let name in paths) {
            let relative = paths[name].split('/')
            relative.shift()
            const bundleDirectory = path.resolve(sourceConfig, '../../')
            absolutePaths[name] = bundleDirectory + '/public/' + relative.join('/')
        }

        return absolutePaths
    },
    getAliasPaths(requireJsConfigPaths, pathOverrides, baseDirectory) {
        const requireConfig = requireUtils.getRequireConfig(requireJsConfigPaths)

        const overrides = _.mapValues(pathOverrides, override => path.resolve(baseDirectory, override))

        const aliasPaths = Object.assign(requireConfig.modulePaths, overrides, {
            backbone: require.resolve('backbone'),
            routes: path.resolve('web/js/routes'),
            general: path.resolve('web/dist/general'),
            paths: path.resolve('web/dist/paths'),
            summernote: path.resolve('node_modules/summernote/dist/summernote.min.js'),
            'fos-routing-base': path.resolve('vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router'),
            CodeMirror: path.resolve('node_modules/codemirror/lib/codemirror')
        })

        mkdirp('web/dist', function() {
            console.log('→ Starting to dump module config to dist'.green)
            requireUtils.exportModule('general.js', JSON.stringify(requireConfig.config))
            requireUtils.exportModule('paths.js', JSON.stringify(aliasPaths))
        })

        return aliasPaths;
    }
}

module.exports = requireUtils
