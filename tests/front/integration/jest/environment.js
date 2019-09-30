const NodeEnvironment = require('jest-environment-node');
const puppeteer = require('puppeteer');
const os = require('os');
const path = require('path');
const { readFileSync } = require('fs');

const DIR = path.join(os.tmpdir(), 'jest_puppeteer_global_setup');
const baseFile = readFileSync(`${process.cwd()}/public/test_dist/index.html`, 'utf-8');
const UserBuilder = require('../../common/builder/user');
const adminUser = new UserBuilder().withUsername('admin').build();

class PuppeteerEnvironment extends NodeEnvironment {
  constructor(config) {
    super(config);
  }

  async setup() {
    await super.setup();
    // get the wsEndpoint
    const wsEndpoint = readFileSync(path.join(DIR, 'wsEndpoint'), 'utf8');
    if (!wsEndpoint) {
      throw new Error('wsEndpoint not found');
    }

    // connect to puppeteer
    this.global.__BROWSER__ = await puppeteer.connect({
      browserWSEndpoint: wsEndpoint,
    });

    const page = await this.global.__BROWSER__.newPage();
    await page.setRequestInterception(true);

    page.on('request', req => {
      if (req.url() === 'http://pim.com/') {
        return req.respond({
          contentType: 'text/html;charset=UTF-8',
          body: baseFile,
        });
      }

      if (req.url() === 'http://pim.com/js/extensions.json') {
        return req.respond({
          contentType: 'application/json',
          body: JSON.stringify(require(`${process.cwd()}/public/js/extensions.json`))
        })
      }

      if (req.url() === 'http://pim.com/rest/security/') {
        return req.respond({
          contentType: 'application/json',
          body: JSON.stringify(require('../responses/rest-security.json'))
        })
      }

      if (req.url() === 'http://pim.com/rest/user/') {
        return req.respond({
          contentType: 'application/json',
          body: JSON.stringify(adminUser)
        })
      }

      if (req.url() === 'http://pim.com/localization/format/date') {
        return req.respond({
          contentType: 'application/json',
          body: JSON.stringify(require('../responses/date-format.json'))
        })
      }

      if (req.url() === 'http://pim.com/configuration/locale/rest?activated=true') {
        return req.respond({
          contentType: 'application/json',
          body: JSON.stringify(require('../responses/activated-locales.json'))
        })
      }

      if (req.url() === 'http://pim.com/js/translation/en_US.js') {
        return req.respond({
          contentType: 'application/json',
          body: JSON.stringify(readFileSync(`${process.cwd()}/public/js/translation/en_US.js`, 'utf-8'))
        })
      }
    });

    await page.goto('http://pim.com');
    await page.addStyleTag({ content: readFileSync(`${process.cwd()}/public/css/pim.css`, 'utf-8')})
    await page.setViewport({ width: 1920, height: 1080 })
    await page.evaluate(async () => await require('pim/fetcher-registry').initialize());
    await page.evaluate(async () => await require('pim/init')());
    await page.evaluate(async () => await require('pim/user-context').initialize());
    await page.evaluate(async () => await require('pim/date-context').initialize());
    await page.evaluate(async () => await require('pim/init-translator').fetch());
    await page.evaluate(async () => await require('oro/init-layout')());

    this.global.__PAGE__ = page;
  }

  async teardown() {
    await super.teardown();
  }

  runScript(script) {
    return super.runScript(script);
  }
}

module.exports = PuppeteerEnvironment;
