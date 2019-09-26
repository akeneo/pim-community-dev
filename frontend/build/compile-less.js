require('colors')
const { resolve, basename } = require('path')
const { writeFileSync, readFileSync, existsSync, mkdirSync } = require('fs')
const rootDir = process.cwd()
const isDev = process.argv && process.argv.indexOf('--dev') > -1;
const lessc = require('less')
const glob = require('glob')

// A plugin to rewrite the image urls
const RewriteImageURLs = require('./less-rewrite-urls')

// The file that contains the paths of all required bundles of the PIM
const BUNDLE_REQUIRE_PATH = resolve(rootDir, './public/js/require-paths')

// The file path for each bundle that imports all the .less files
const BUNDLE_LESS_INDEX_PATH = 'Resources/public/less/index.less'

// The final output path for all the CSS of the PIM
const OUTPUT_CSS_PATH = 'public/css/pim.css'

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
                .replace(/(^.+)((?<=src).*[^vendor])(?=\/src)\//gm, '')
    })

    const bundleImports = []

    console.log('\nStarting LESS compilation\n'.green)

    for (filePath of indexFiles) {
        bundleImports.push(getFileContents(filePath))
    }

    console.log('\n')
    return bundleImports.join('')
}

function collectOverrideImports(bundlePaths) {
    const overrideFiles = [];

    bundlePaths.forEach((bundlePath) => {
        const resolvedPath = bundlePath.replace(/(^.+)[^vendor](?=\/src|\/vendor)\//gm, '')
        const overrides = glob.sync(`${resolvedPath}/**/overrides-**.less`)
        overrides.forEach(override => overrideFiles.push(override));
    });

    const overrideImports = {}

    for(filePath of overrideFiles) {
        const fileName = basename(filePath, '.less');
        overrideImports[fileName] = getFileContents(filePath)
    }

    return overrideImports;
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
 * @param {String} fileName
 */
function writeCSSOutput(css, fileName) {
    const folderPath = resolve(rootDir, 'public/css')
    const filePath = resolve(rootDir, fileName);

    try {
        if (existsSync(folderPath) === false) {
            mkdirSync(folderPath, { recursive: true });
        }
        writeFileSync(filePath, css, 'utf-8')
        console.log(`✓ Saved CSS to ${fileName}`.green)
    } catch (e) {
        console.log(`❌ Error writing CSS ${e.message}`.red)
    }
}

function convertLessToCss(cssFiles, fileName) {
    lessc.render(cssFiles, {
        sourceMap: {
            sourceMapFileInline: isDev
        },
        compress: false,
        plugins: [new RewriteImageURLs({
            // Remove the 'web' part of the image/font urls
            replace: [{
                search: './public/bundles',
                replace: '/bundles'
            }]
        })]
    }).then(
        output => writeCSSOutput(output.css, fileName),
        error => {
            formatParseError(error)
            process.exit(1)
        })
        .catch(error => console.log('Error', error))
}

const index = collectBundleImports(bundlePaths)
const overrides = collectOverrideImports(bundlePaths)

convertLessToCss(index, OUTPUT_CSS_PATH)

Object.entries(overrides).forEach(([fileName, contents]) => {
    convertLessToCss(contents, `public/css/${fileName}.css`);
})
