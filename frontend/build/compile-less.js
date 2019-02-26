require('colors')
const { dirname, resolve } = require('path')
const { writeFileSync, readFileSync, existsSync } = require('fs')
const rootDir = process.cwd()
const isDev = process.argv && process.argv.indexOf('--dev') > -1;
const lessc = require('less')

// A plugin to rewrite the image urls
const RewriteImageURLs = require('./less-rewrite-urls')

// The file that contains the paths of all required bundles of the PIM
const BUNDLE_REQUIRE_PATH = resolve(rootDir, './web/js/require-paths')

// The file path for each bundle that imports all the .less files
const BUNDLE_LESS_INDEX_PATH = 'Resources/public/less/index.less'

// The final output path for all the CSS of the PIM
const OUTPUT_CSS_PATH = 'web/css/pim.css'

if (!existsSync(`${BUNDLE_REQUIRE_PATH}.js`)) {
    console.log(`${BUNDLE_REQUIRE_PATH} does not exist - Run "bin/console pim:installer:dump-require-paths" and try again.`.red)
    process.exit(1)
}

const bundlePaths = require(BUNDLE_REQUIRE_PATH)

/**
 * Get the file contents of a given path as a string
 *
 * @param {string} filePath
 */
function getFileContents(filePath) {
    try {
        const fileContents = readFileSync(filePath, 'utf-8')
        console.log(`‣ ${filePath}`.blue)
        return fileContents
    } catch (e) { }
}

/**
 * Return the contents of each index.less file from each bundle
 *
 * @param {array} bundlePaths An array of directories of each required bundle
 */
function collectBundleImports(bundlePaths) {
    // Make each path relative
    const indexFiles = bundlePaths.map(bundlePath => {
        return `${bundlePath}/${BUNDLE_LESS_INDEX_PATH}`
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

/**
 * Format a lessjs compilation error
 *
 * @param {Error} error A LESS error coming from the lessjs parser
 */
function formatParseError(error) {
    console.log(`Error compiling less: ${error.message}\n\n`.red, `${error.filename}:${error.line}:${error.column}`.yellow)
    console.log(error.extract.map(line => `>${line}\n`.red).join(''))
}

/**
 * Write the final CSS output into a file
 *
 * @param {String} css The combined CSS from each bundle
 */
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
        sourceMapFileInline: isDev
    },
    compress: true,
    plugins: [new RewriteImageURLs({
        // Remove the 'web' part of the image/font urls
        replace: [{
            search: './web/bundles',
            replace: '/bundles'
        }]
    })]
}).then(
    output => writeCSSOutput(output.css),
    error => {
        formatParseError(error)
        process.exit(1)
    })
    .catch(error => console.log('Error', error))
