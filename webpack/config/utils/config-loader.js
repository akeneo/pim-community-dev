const utils = require('loader-utils');
const path = require('path');
const {red, grey, green} = require('colors');

/**
 * This method replaces the "@my/module/to/load" by require('my/module/to/load') in the configuration
 */
function replaceRequire(config) {
    return config.replace(/\"\@[^"]*\"/gm, function(match) {
        return `require('${match.substr(2, match.length - 3)}')`;
    });
}

/**
 * Injects the requirejs module config into required webpack modules
 * @param  {String} content The content of the required module
 * @return {String}         Returns a string with the original content and the injected config
 */
module.exports = function(content) {
    const options = utils.getOptions(this);

    this.cacheable();

    if (content.indexOf('__moduleConfig') === 0) {
        return content;
    }

    let modulePath = this._module.userRequest;
    const moduleExt = path.extname(modulePath);

    modulePath = modulePath.replace(moduleExt, '');

    const [moduleAlias] =
        Object.entries(options.aliases).find(([, relativeModulePath]) => modulePath.endsWith(relativeModulePath)) || [];

    if (undefined === moduleAlias) {
        options.debug && console.log(red(`No alias found for module: ${modulePath}`));

        return content;
    }

    const moduleConfig = JSON.stringify(options.configMap[moduleAlias] || {});

    options.debug && moduleConfig === '{}' && console.log(grey(`${moduleAlias} ${JSON.stringify(moduleConfig)} `));
    options.debug && moduleConfig !== '{}' && console.log(green(`${moduleAlias} ${JSON.stringify(moduleConfig)}`));

    return `var __moduleConfig = ${replaceRequire(moduleConfig)} ; ${content}`;
};
