const requirePaths = require('../web/js/require-paths.js');
const { dirname } = require('path');
const glob = require('glob');
const { parse } = require('yamljs');
const { readFileSync, writeFileSync } = require('fs');
const deepMerge = require('deepmerge');

const formExtensions =  {
    collectPaths: () => {
        const extensionPaths = requirePaths.map(req => {
            return glob.sync(`${dirname(req)}/{form_extensions/**/*.yml,form_extensions.yml}`)
        });

        return [].concat.apply([], extensionPaths);
    },

    merge(extensionPaths = []) {
        const extensions = extensionPaths.map(path => parse(readFileSync(path, 'utf8')));
        const merged = deepMerge.all(extensions);
        const formattedExtensions = {};
        let i = 0;

        for (let extension in merged.extensions) {
            const obj = merged.extensions[extension];
            obj.code = extension;
            formattedExtensions[i] = obj;
            i++;
        }

        console.log(formattedExtensions)

        return {
            attribute_fields: merged.attribute_fields,
            extensions: formattedExtensions
        }
    },

    dump: (extensions) => {
        writeFileSync('./web/js/extensions.json', JSON.stringify(extensions));
    }
}

const paths = formExtensions.merge(formExtensions.collectPaths());
formExtensions.dump(paths);

module.exports = formExtensions;
