const rootDir = process.cwd();
const path = require('path');
const fs = require('fs')
const lessc = require('less')
const RewriteImageURLs = require('./less-rewrite-urls');
const bundlePaths = require(path.resolve(rootDir, './web/js/require-paths'));
const BUNDLE_LESS_INDEX_PATH = 'public/less/index.less'
const OUTPUT_CSS_PATH = 'web/css/pim.css'

function getFileContents(filePath) {
    try {
        const fileContents = fs.readFileSync(filePath, {
            encoding: 'utf-8'
        })

        console.log('✓', filePath)
        return fileContents
    } catch(e) {}
}

function collectBundleImports(bundlePaths) {
    const indexFiles = bundlePaths.map(bundlePath => {
        return path.dirname(bundlePath)
            .replace('config', BUNDLE_LESS_INDEX_PATH)
            .replace(/(^.+)[^vendor](?=\/src|\/vendor)\//gm, '')
    })

    const bundleImports = []

    console.log('Compiling less\n')
    for (filePath of indexFiles) {
        bundleImports.push(getFileContents(filePath))
    }

    console.log('\n')
    return bundleImports.join('');
}

function formatParseError(error) {
    console.log(`Error compiling less: ${error.message}\n\n`, `${error.filename}:${error.line}:${error.column}`)
    console.log(error.extract.map(line => `>${line}\n`).join(''))
}

const bundleImports = collectBundleImports(bundlePaths)

lessc.render(
    bundleImports,
    {
        plugins: [
            new RewriteImageURLs({
                replace: [{
                    search: './web/bundles',
                    replace: '/bundles' }
                ]
            })
        ],
        sourceMap: {sourceMapFileInline: true}
    }
).then(function(output) {
        try {
            fs.writeFileSync(path.resolve(rootDir, OUTPUT_CSS_PATH), output.css, 'utf-8')
            console.log(`Successfully compiled to ${OUTPUT_CSS_PATH}`)
        } catch(e) {
            console.log('Error writing file', e)
        }
        // output.map = string of sourcemap
}, function(error) {
    formatParseError(error)
    process.exit(1)
}).catch(error => console.log('Other error', error));
