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
    config: []
}

function getRelativeBundlePath(path) {
    return path.replace(/(^.+)[^vendor](?=\/src|\/vendor)\//gm, '')
}

function getFileContents(path) {
    try {
        return parse(readFileSync(path, 'utf-8'))
    } catch (e) {
        console.log('Error', e)

        process.exit(1);
    }
}

function getExtensionsFromRequiredBundles() {
    const requiredBundles = require(BUNDLE_REQUIRE_PATH);
    const formExtensions = []

    requiredBundles.forEach(dir => {
        formExtensions.push(glob.sync(`${dir}/Resources/config/{form_extensions/**/*.yml,form_extensions.yml}`))
    })

    return [].concat.apply([], formExtensions);
}

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
        extensions: configuredExtensions
    }
}

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

