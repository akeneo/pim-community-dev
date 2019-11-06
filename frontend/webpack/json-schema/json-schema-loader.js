/* eslint-env es6 */
const path = require('path');
const fs = require('fs');
const utils = require('loader-utils');
const compile = require('./compile');

const defaults = {
  typescriptExt: '.ts',
  jsonSchemaExt: '.schema.json',
};

module.exports = async function (content) {
  const options = Object.assign({}, defaults, utils.getOptions(this));
  const schemaPath = this.resourcePath;
  const directory = path.dirname(schemaPath);
  const filename = path.basename(schemaPath, options.jsonSchemaExt);
  const typescriptPath = path.join(directory, filename) + options.typescriptExt;

  await compile(schemaPath, typescriptPath);

  return content;
};
