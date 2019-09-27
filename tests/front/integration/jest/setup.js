const puppeteer = require('puppeteer')
const fs = require('fs')
const mkdirp = require('mkdirp')
const path = require('path')
const os = require('os')

const DIR = path.join(os.tmpdir(), 'jest_puppeteer_global_setup')

module.exports = async function() {
  const browser = await puppeteer.launch({
      ignoreHTTPSErrors: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080'],
      headless: false,
      devtools: true,
      defaultViewport: {
        width: 1920,
        height: 1080
      }
    });
  // store the browser instance so we can teardown it later
  global.__BROWSER__ = browser;

  // file the wsEndpoint for TestEnvironments
  mkdirp.sync(DIR);
  fs.writeFileSync(path.join(DIR, 'wsEndpoint'), browser.wsEndpoint());
};
