require('colors')
const rootDir = process.cwd()
const { resolve, dirname } = require('path')
const glob = require('glob')
const { parse } = require('yamljs')
const { readFileSync, writeFileSync } = require('fs')
const deepmerge = require('deepmerge')
const _ = require('lodash');

console.log('Updating form extensions.json'.blue)

const BUNDLE_REQUIRE_PATH = resolve(rootDir, './web/js/require-paths')
const EXTENSIONS_JSON_PATH = 'web/js/extensions.json'

const EXTENSION_DEFAULTS = {
    module: null,
    parent: null,
    targetZone: 'self',
    zones: [],
    aclResourceId: null,
    config: [],
    position: 100
}

/**
 * Get the bundle path relative to the source folder
 *
 * @param {string} path
 */
function getRelativeBundlePath(path) {
    return path.replace(/(^.+)[^vendor](?=\/src|\/vendor)\//gm, '')
}

/**
 * Read a file and return the contents as a string
 *
 * @param {string} path
 */
function getFileContents(path) {
    try {
        return parse(readFileSync(path, 'utf-8'))
    } catch (e) {
        console.log('Error', e)

        process.exit(1);
    }
}

/**
 * Given a list of bundles required by the app, return a list of form extension .yml files
 */
function getExtensionsFromRequiredBundles() {
    const requiredBundles = require(BUNDLE_REQUIRE_PATH);
    const bundleDirectories = requiredBundles.map(
        bundle => getRelativeBundlePath(bundle)
    )

    const formExtensions = []

    bundleDirectories.forEach(dir => {
        formExtensions.push(glob.sync(`${dir}/Resources/config/{form_extensions/**/*.yml,form_extensions.yml}`))
    })

    return [].concat.apply([], formExtensions);
}

/**
 * Get the form extension configuration from the .yml files and merge them
 *
 * Returns an object containing the attribute fields and form extensions sorted by position
 *
 * @param {string} paths
 */
function mergeExtensions(paths) {
    const config = paths.map(path => getFileContents(path))
    const merged = deepmerge.all(config)
    const configuredExtensions = {}
    let i = 0;

    for (let extension in merged.extensions) {
        const extensionConfig = _.defaults(
            merged.extensions[extension],
            EXTENSION_DEFAULTS,
            { code: extension },
        )

        configuredExtensions[i] = extensionConfig
        i++
    }

    return {
        attribute_fields: merged.attribute_fields,
        extensions: _.sortBy(configuredExtensions, 'position')
    }
}

/**
 * Writes the merged form extensions to a file at web/js/extensions.json
 *
 * @param {Object} contents
 */
function writeExtensionsJSON(contents) {
    try {
        writeFileSync(EXTENSIONS_JSON_PATH, JSON.stringify(contents), {
            encoding: 'utf-8'
        })
    } catch (e) {
        console.log('Error', e)

        process.exit(1)
    }
}

const extensions = getExtensionsFromRequiredBundles()
const mergedExtensions = mergeExtensions(extensions)

writeExtensionsJSON(mergedExtensions)
