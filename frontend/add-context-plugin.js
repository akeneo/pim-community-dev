/* eslint-env es6 */
var fs = require('fs')
const path = require('path')

module.exports = function(extras, dir) {
    this.extras = extras || [];

    this.apply = function(compiler) {
        compiler.plugin('context-module-factory', function(cmf) {
            cmf.plugin('alternatives', function(items, callback) {
                items = items.map(function(item) {
                    var request = item.request;

                    try {
                        // Make sure the webpack context map uses the symlinked path
                        // request = fs.realpathSync(item.request, 'utf8')
                        request = path.resolve(item.request)
                        // console.log(path.resolve(item.request))
                    } catch (e) {}

                    item.request = request

                    return item
                })

                return callback(null, items);
            });
        });
    }

    return this;
}
