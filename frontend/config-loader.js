/* eslint-env es6 */

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

    const ext = '.js'
    const aliases = _.invert(this.options.resolve.alias)
    const aliasKeys = _.mapKeys(aliases, (alias, key) => key.replace(ext, ''))
    const moduleUrl = this.resourcePath.replace(ext, '')
    const moduleName = aliasKeys[moduleUrl]
    const moduleConfig = configMap[formatModuleName(moduleName)]

    return `var __moduleConfig = ${JSON.stringify(moduleConfig)} ; ${content}`;
}
