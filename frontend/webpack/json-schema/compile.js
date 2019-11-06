/* eslint-env es6 */
const fs = require('fs');
const { compileFromFile } = require('json-schema-to-typescript');
const colors = require('colors');

module.exports = async function compile(schemaPath, typescriptPath) {
  const content = await compileFromFile(schemaPath);
  fs.writeFileSync(typescriptPath, content);
};
