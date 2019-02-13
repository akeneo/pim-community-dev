const rootDir = process.cwd();
const path = require('path');
const bundlePaths = require(path.resolve(rootDir, './web/js/require-paths'));
const BUNDLE_LESS_PATH = 'public/less/index.less'
const fs = require('fs')
const lessc = require('less')

function collectBundleStyles(bundlePaths) {
    const styles = bundlePaths.map(bundle => {
        return  path.dirname(bundle)
                .replace('config', BUNDLE_LESS_PATH);
    })

    const imports = []

    for (style of styles) {
        const absolutePath = path.resolve(rootDir, style.replace('/srv/pim', '.'))

        try {
            const contents = fs.readFileSync(absolutePath, {
                encoding: 'utf-8'
            })

            imports.push(contents)
            console.log('Added', absolutePath)
        } catch(e) {}
    }

    return imports;
}

const appStyles = collectBundleStyles(bundlePaths).join('')
const compiledStyles = lessc.render(appStyles, {

})
    .then(function(output) {

        try {
            fs.writeFileSync(path.resolve(rootDir, './web/css/pim.css'), output.css, 'utf-8')
        } catch(e) {
            console.log("Error writing file", e)
        }
        // output.css = string of css
        // output.map = string of sourcemap
        // output.imports = array of string filenames of the imports referenced
    },
    function(error) {
        console.log("Error rendering", error)
    }).catch(error => console.log('Other error', error));


