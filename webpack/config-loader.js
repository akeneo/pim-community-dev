/* eslint-env es6 */
const utils = require('loader-utils');
const path = require('path');
const hasModule = content => content.indexOf('__moduleConfig') >= 0;
const {chain} = require('lodash');

function formatModuleName(name) {
  if (!name) return;

  return name.replace('pimcommunity', 'pim');
}

/**
 * This method replaces the "@my/module/to/load" by require('my/module/to/load') in the configuration
 */
function replaceRequire(config) {
  return config.replace(/\"\@[^"]*\"/gm, function(match) {
    return "require('" + match.substr(2, match.length - 3) + "')";
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
  if (!hasModule(content)) return content;

  const moduleAlias = this._module.rawRequest;
  const moduleConfig = JSON.stringify(options.configMap[moduleAlias] || {});

  return `var __moduleConfig = ${replaceRequire(moduleConfig)} ; ${content}`;
};
