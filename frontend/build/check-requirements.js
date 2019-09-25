require('colors')
const { existsSync } = require('fs')
const BUNDLE_REQUIRE_PATH = './public/js/require-paths.js'
const ROUTES_PATH = './public/js/routes.js'

console.log('Checking PIM frontend requirements'.blue)

if (!existsSync(BUNDLE_REQUIRE_PATH)) {
    console.log(`${BUNDLE_REQUIRE_PATH} does not exist - Run "bin/console pim:installer:dump-require-paths" and try again.`.red)
    process.exit(1)
}

if (!existsSync(ROUTES_PATH)) {
    console.log(`${ROUTES_PATH} does not exist - Run "bin/console --ansi fos:js-routing:dump --target=public/js/routes.js" and try again.`.red)
    process.exit(1)
}
