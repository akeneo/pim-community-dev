/* eslint-env es6 */
var fs = require('fs')
const path = require('path')

module.exports = function(extras) {
    this.extras = extras || []

    this.apply = function(compiler) {
        compiler.plugin('context-module-factory', function(cmf) {
            cmf.plugin('alternatives', function(items, callback) {
                items = items.map(function(item) {
                    var request = item.request

                    try {
                        request = path.resolve(item.request)
                    } catch (e) {}

                    item.request = request

                    return item
                })

                return callback(null, items)
            })
        })
    }

    return this
}
