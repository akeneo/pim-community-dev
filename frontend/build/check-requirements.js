require('colors')
const { existsSync } = require('fs')
const BUNDLE_REQUIRE_PATH = './web/js/require-paths.js'
const ROUTES_PATH = './web/js/routes.js'
const EXTENSIONS_PATH = './web/js/extensions.json'

console.log('Checking PIM frontend requirements'.blue)

if (!existsSync(BUNDLE_REQUIRE_PATH)) {
    console.log(`${BUNDLE_REQUIRE_PATH} does not exist - Run "bin/console pim:installer:dump-require-paths" and try again.`.red)
    process.exit(1)
}

if (!existsSync(ROUTES_PATH)) {
    console.log(`${ROUTES_PATH} does not exist - Run "bin/console --ansi fos:js-routing:dump --target=web/js/routes.js" and try again.`.red)
    process.exit(1)
}

if (!existsSync(EXTENSIONS_PATH)) {
    console.log(`${EXTENSIONS_PATH} does not exist - Run "yarn update-extensions" and try again.`.red)
    process.exit(1)
}
