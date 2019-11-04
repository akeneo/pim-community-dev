/* eslint-env es6 */
const path = require('path')
const fs = require('fs')
const { compileFromFile } = require('json-schema-to-typescript')

const defaults = {
  typescriptExt: '.ts',
  jsonSchemaExt: '.schema.json',
  exclude: /node_modules/,
}

async function compile(schemaPath, typescriptPath) {
  const content = await compileFromFile(schemaPath)
  fs.writeFileSync(typescriptPath, content)
}

class JsonSchemaPlugin {
  constructor(options = {}) {
    this.options = Object.assign({}, defaults, options)
  }

  apply(compiler) {
    const { typescriptExt, jsonSchemaExt, exclude } = this.options

    compiler.resolverFactory.plugin('resolver normal', function (resolver) {
      resolver.hooks.resolve.tapAsync('JsonSchemaPlugin', async function (request, resolveContext, callback) {
        const identifier = request.request
        const typescriptPath = identifier + typescriptExt
        const jsonSchemaPath = identifier + jsonSchemaExt

        for (let module of compiler.options.resolve.modules) {
          let typescriptAbsolutePath = path.join(module, typescriptPath)

          if (typescriptAbsolutePath.match(exclude)) {
            continue
          }

          let jsonSchemaAbsolutePath = path.join(module, jsonSchemaPath)

          if (fs.existsSync(jsonSchemaAbsolutePath)) {
            await compile(jsonSchemaAbsolutePath, typescriptAbsolutePath)
            return callback(null, { path: typescriptAbsolutePath })
          }
        }

        callback()
      })
    })
  }
}

module.exports = JsonSchemaPlugin
