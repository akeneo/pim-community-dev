const requirePaths = require('../web/js/require-paths.js');
const { dirname } = require('path');
const glob = require('glob');
const { parse } = require('yamljs');
const { readFileSync } = require('fs');
const deepMerge = require('deepmerge');
const _ = require('lodash');

const formExtensions =  {
    collectPaths: () => {
        const extensionPaths = requirePaths.map(req => {
            return glob.sync(`${dirname(req)}/{form_extensions/**/*.yml,form_extensions.yml}`);
        });

        return [].concat.apply([], extensionPaths);
    },

    merge(extensionPaths = []) {
        const extensions = extensionPaths.map(path => parse(readFileSync(path, 'utf8')));
        const merged = deepMerge.all(extensions);
        const formattedExtensions = {};
        let i = 0;

        for (let extension in merged.extensions) {
            const obj = _.defaults(merged.extensions[extension], {
                code: extension,
                module: null,
                parent: null,
                targetZone: 'self',
                zones: [],
                aclResourceId : null,
                config: []
            });

            formattedExtensions[i] = obj;
            i++;
        }

        return {
            attribute_fields: merged.attribute_fields,
            extensions: formattedExtensions
        };
    }
};

module.exports = formExtensions;
