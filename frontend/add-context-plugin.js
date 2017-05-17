/* eslint-env es6 */
var fs = require('fs')

module.exports = function(extras) {
    this.extras = extras || [];

    this.apply = function(compiler) {
        compiler.plugin('context-module-factory', function(cmf) {
            cmf.plugin('alternatives', function(items, callback) {
                items = items.map(function(item) {
                    var request = item.request;

                    try {
                        // Make sure the webpack context map uses the symlinked path
                        request = fs.realpathSync(item.request, 'utf8')
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
