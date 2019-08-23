const NodeEnvironment = require('jest-environment-node');
const puppeteer = require('puppeteer');
const fs = require('fs');
const os = require('os');
const path = require('path');
const DIR = path.join(os.tmpdir(), 'jest_puppeteer_global_setup');
const baseFile = fs.readFileSync(`${process.cwd()}/web/test_dist/index.html`, 'utf-8');
const extensions = fs.readFileSync(`${process.cwd()}/web/js/extensions.json`, 'utf-8');
const restSecurity = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/rest_security.json`, 'utf-8');
const activatedLocales = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/activated_locales.json`, 'utf-8');
const user = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/user.json`, 'utf-8');
const translations = fs.readFileSync(path.join(process.cwd(), './web/js/translation/en_US.js'), 'utf-8');
const pimCSS = fs.readFileSync(`${process.cwd()}/web/css/pim.css`, 'utf-8');
const dateFormat = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/date_format.json`, 'utf-8');

class PuppeteerEnvironment extends NodeEnvironment {
  constructor(config) {
    super(config);
  }

  async setup() {
    await super.setup();
    // get the wsEndpoint
    const wsEndpoint = fs.readFileSync(path.join(DIR, 'wsEndpoint'), 'utf8');
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

      if (req.url() === 'http://pim.com/rest/user/') {
        return req.respond({
          contentType: 'application/json',
          body: user,
        });
      }

      if (req.url() === 'http://pim.com/rest/security/') {
        return req.respond({
          contentType: 'application/json',
          body: restSecurity
        })
      }

      if (req.url() === 'http://pim.com/js/extensions.json') {
        return req.respond({
          contentType: 'application/json',
          body: extensions
        });
      }

      if(req.url().includes('/notification/count_unread') ||
      req.url().includes('/notification/list?skip=0')) {
        return req.respond({
          contentType: 'application/json',
          body: 0
        });
      }

      if(req.url().includes('thumbnail_small') ||
        req.url().includes('style.css') ||
        req.url().includes('favicon.ico') ||
        req.url().includes('info-user.png')) {
        return req.respond({
          contentType: 'application/json',
          status: 200
        });
      }

      if(req.url() === 'http://pim.com/configuration/locale/rest?activated=true') {
        return req.respond({
          contentType: 'application/json',
          body: activatedLocales
        })
      }

      if (req.url().includes('/js/translation')) {
        return req.respond({
          contentType: 'application/json',
          body: translations,
        });
      }

      if (req.url().includes('/localization/format/date')) {
        return req.respond({
          contentType: 'application/json',
          body: dateFormat
        });
      }
    });

    await page.goto('http://pim.com');
    await page.addStyleTag({ content: pimCSS });
    await page.evaluate(async () => await require('pim/fetcher-registry').initialize());
    // await page.evaluate(async () => await require('pim/init')());
    // await page.evaluate(async () => await require('pim/user-context').initialize());
    // await page.evaluate(async () => await require('pim/date-context').initialize());
    // await page.evaluate(async () => await require('pim/init-translator').fetch());
    // await page.evaluate(async () => await require('oro/init-layout')());

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
