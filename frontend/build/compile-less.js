require('colors')
const { dirname, resolve } = require('path')
const rootDir = process.cwd()
const { writeFileSync, readFileSync, existsSync } = require('fs')
const lessc = require('less')
const RewriteImageURLs = require('./less-rewrite-urls')

const BUNDLE_REQUIRE_PATH = resolve(rootDir, './web/js/require-paths')
const BUNDLE_LESS_INDEX_PATH = 'public/less/index.less'
const OUTPUT_CSS_PATH = 'web/css/pim.css'
const SOURCEMAP_CSS_PATH = 'web/css/pim.css.map'

if (!existsSync(`${BUNDLE_REQUIRE_PATH}.js`)) {
    console.error(`web/js/require-paths.js does not exist - Run "bin/console pim:installer:dump-require-paths" and try again.`.red)

    process.exit(1)
}

const bundlePaths = require(BUNDLE_REQUIRE_PATH)

function getFileContents(filePath) {
    try {
        const fileContents = readFileSync(filePath, 'utf-8')
        console.log(`‣ ${filePath}`.blue)
        return fileContents
    } catch (e) { }
}

function collectBundleImports(bundlePaths) {
    const indexFiles = bundlePaths.map(bundlePath => {
        return dirname(bundlePath)
            .replace('config', BUNDLE_LESS_INDEX_PATH)
            .replace(/(^.+)[^vendor](?=\/src|\/vendor)\//gm, '')
    })

    const bundleImports = []

    console.log('\nStarting LESS compilation\n'.green)

    for (filePath of indexFiles) {
        bundleImports.push(getFileContents(filePath))
    }

    console.log('\n')
    return bundleImports.join('')
}

function formatParseError(error) {
    console.log(`Error compiling less: ${error.message}\n\n`.red, `${error.filename}:${error.line}:${error.column}`.yellow)
    console.log(error.extract.map(line => `>${line}\n`.red).join(''))
}

function writeCSSOutput(css) {
    try {
        writeFileSync(resolve(rootDir, OUTPUT_CSS_PATH), css, 'utf-8')
        console.log(`✓ Saved CSS to ${OUTPUT_CSS_PATH}`.green)
    } catch (e) {
        console.log(`❌ Error writing CSS ${e.message}`.red)
    }
}

lessc.render(collectBundleImports(bundlePaths), {
    sourceMap: {
        sourceMapFileInline: true
    },
    plugins: [new RewriteImageURLs({
        replace: [{
            search: './web/bundles',
            replace: '/bundles'
        }]
    })]
}).then(function (output) {
    writeCSSOutput(output.css)
}, function (error) {
    formatParseError(error)
    process.exit(1)
}).catch(error => console.log('Other error', error))
