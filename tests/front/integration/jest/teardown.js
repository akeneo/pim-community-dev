const rimraf = require('rimraf')
const os = require('os')
const path = require('path')

const DIR = path.join(os.tmpdir(), 'jest_puppeteer_global_setup')

module.exports = async function() {
  // close the browser instance
  if (!process.env.DEBUG) {
    await global.__BROWSER__.close();
  }

  // clean-up the wsEndpoint file
  rimraf.sync(DIR);
};
