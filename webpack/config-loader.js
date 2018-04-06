/* eslint-env es6 */
const utils = require('loader-utils');
const hasModule = (content) => content.indexOf('__moduleConfig') >= 0;

/**
 * Injects the requirejs module config into required webpack modules
 * @param  {String} content The content of the required module
 * @return {String}         Returns a string with the original content and the injected config
 */
module.exports = function(content) {
    const options = utils.getOptions(this);

    this.cacheable();
    if (!hasModule(content)) return content;

    const moduleAlias = this._module.rawRequest;
    const moduleConfig = options.configMap[moduleAlias];

    return `var __moduleConfig = ${JSON.stringify(moduleConfig)} ; ${content}`;
};