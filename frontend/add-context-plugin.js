'use strict';

const fs = require('fs')

class AddToContextPlugin {
  constructor(extras) {
    this.extras = extras || [];
  }

  apply(compiler) {
     compiler.plugin('context-module-factory', function (cmf) {
        cmf.plugin('alternatives', function (items, callback) {
            items = items.map((item) => {
                let request = item.request;

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
}

module.exports = AddToContextPlugin;
