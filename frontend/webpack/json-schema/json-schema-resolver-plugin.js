/* eslint-env es6 */
const path = require('path');
const fs = require('fs');
const compile = require('./compile');

const defaults = {
  typescriptExt: '.ts',
  jsonSchemaExt: '.schema.json',
  exclude: /node_modules/,
};

class JsonSchemaResolverPlugin {
  constructor(options = {}) {
    this.options = Object.assign({}, defaults, options);
  }

  apply(resolver) {
    const { typescriptExt, jsonSchemaExt, exclude } = this.options;

    resolver.ensureHook('raw-file');
    resolver.getHook('raw-file').tapAsync('JsonSchemaResolverPlugin', async function (request, resolveContext, callback) {
      const identifier = request.request || request.path;
      const jsonSchemaPath = identifier + jsonSchemaExt;
      const typescriptPath = identifier + typescriptExt;

      // skip paths with extensions, we are only looking for unresolved imports
      if ('' !== path.extname(identifier)) {
        return callback();
      }

      if (identifier.match(exclude)) {
        return callback();
      }

      // There is a context only for cases when the file is requested from special sources,
      // eg: web/js/module-registry.js
      if (undefined !== request.context) {
        return callback();
      }

      const nextRequest = Object.assign({}, request, { path: jsonSchemaPath });
      resolver.doResolve(resolver.getHook('file'), nextRequest, null, resolveContext, async (err, result) => {
        if (!err && result) {
          await compile(jsonSchemaPath, typescriptPath);
          callback(null, { path: typescriptPath });
        } else {
          callback(err, result);
        }
      });
    });
  }
}

module.exports = JsonSchemaResolverPlugin;
