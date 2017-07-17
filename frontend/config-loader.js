/* eslint-env es6 */
const utils = require('loader-utils')
const path = require('path')
const hasModule = (content) => content.indexOf('__moduleConfig') >= 0
const { chain } = require('lodash')


function formatModuleName(name) {
    if (!name) return

    return name.replace('pimcommunity', 'pim')
}

/**
 * Injects the requirejs module config into required webpack modules
 * @param  {String} content The content of the required module
 * @return {String}         Returns a string with the original content and the injected config
 */
module.exports = function(content) {
    const options = utils.getOptions(this)

    this.cacheable()
    if (!hasModule(content)) return content

    const aliases = chain(this.options.resolve.alias)
                    .invert()
                    .mapValues(alias => alias.replace(/\$$/, ''))
                    .value()

    let modulePath = this._module.rawRequest
    const moduleExt = path.extname(modulePath)

    modulePath = modulePath.replace(moduleExt, '')

    const moduleName = aliases[modulePath]
    const moduleConfig = options.configMap[formatModuleName(moduleName)] || {}

    return `var __moduleConfig = ${JSON.stringify(moduleConfig)} ; ${content}`;
}
