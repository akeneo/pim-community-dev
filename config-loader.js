const hasModule = (content) => content.indexOf('module') >= 0;
const loaderUtils = require('loader-utils')
const _ = require('lodash')

module.exports = function(content) {
    this.cacheable()
    if (!hasModule(content)) return content;

    const ext = '.js'
    const aliases = _.invert(this.options.resolve.alias)
    const aliasKeys = _.mapKeys(aliases, (alias, key) => key.replace(ext, ''))
    const moduleUrl = this.resourcePath.replace(ext, '')
    const moduleName = aliasKeys[moduleUrl]
    return `var __moduleName = '${moduleName}'; ${content}`;
}
