/* eslint-env es6 */

const path = require('path')
const hasModule = (content) => content.indexOf('module') >= 0;
const configMap = require('../web/dist/general.js')
const _ = require('lodash')


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
    this.cacheable()
    if (!hasModule(content)) return content;

    const aliases = _.invert(this.options.resolve.alias)
    let modulePath = this.resourcePath
    const moduleExt = path.extname(modulePath)

    modulePath = modulePath.replace(moduleExt, '')

    const moduleName = aliases[modulePath]
    const moduleConfig = configMap[formatModuleName(moduleName)] || {}

    return `var __moduleConfig = ${JSON.stringify(moduleConfig)} ; ${content}`;
}
