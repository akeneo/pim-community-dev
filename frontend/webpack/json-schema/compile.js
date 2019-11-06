/* eslint-env es6 */
const fs = require('fs');
const { compileFromFile } = require('json-schema-to-typescript');
const colors = require('colors');

const COMPILED = 1;
const SKIPPED = 2;

module.exports = async function compile(schemaPath, typescriptPath) {
  const content = await compileFromFile(schemaPath);

  // if (fs.existsSync(typescriptPath) &&
  //   fs.readFileSync(typescriptPath).toString() === content) {
  //   return SKIPPED;
  // }

  fs.writeFileSync(typescriptPath, content);

  console.log(colors.grey('JSON schema to typescript: ', schemaPath));

  // return COMPILED;
};
