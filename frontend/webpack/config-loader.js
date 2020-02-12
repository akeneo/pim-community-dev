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

function findModuleName(options, _module) {

  let moduleName = _module.rawRequest;
  if (options.aliases[moduleName] !== undefined) {
    return moduleName;
  }

  const paths = chain(options.aliases)
    .invert()
    .mapValues(alias => alias.replace(/\$$/, ''))
    .value();
  let modulePath = _module.userRequest;
  modulePath = modulePath.replace(path.extname(modulePath), '');

  return paths[modulePath];
}

/**
 * Injects the requirejs module config into required webpack modules
 * @param  {String} content The content of the required module
 * @return {String}         Returns a string with the original content and the injected config
 */
module.exports = function(content) {
  this.cacheable();
  if (!hasModule(content)) return content;

  const options = utils.getOptions(this);

  // let moduleRequest = this._module.rawRequest;
  //
  //
  // const aliases = chain(options.aliases)
  //   .invert()
  //   .mapValues(alias => alias.replace(/\$$/, ''))
  //   .value();
  //
  // let modulePath = this._module.userRequest;
  // const moduleExt = path.extname(modulePath);
  //
  // modulePath = modulePath.replace(moduleExt, '');
  //
  // const moduleName = aliases[modulePath];
  // if(modulePath === '/home/quentin/Work/pim-community-dev/src/Akeneo/Platform/Bundle/UIBundle/Resources/public/js/router') {
  //   console.log(this._module.rawRequest);
  //   console.log(options.aliases);
  //   console.log(aliases);
  //   // console.log(aliases);
  //   // console.log(modulePath, moduleName);
  // }

  const moduleName = findModuleName(options, this._module);

  const moduleConfig = JSON.stringify(options.configMap[formatModuleName(moduleName)] || {});

  return `var __moduleConfig = ${replaceRequire(moduleConfig)} ; ${content}`;
};
