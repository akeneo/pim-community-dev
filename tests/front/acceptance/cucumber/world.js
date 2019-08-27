const puppeteer = require('puppeteer');
const UserBuilder = require('../../common/builder/user');
const LocaleBuilder = require('../../common/builder/locale');
const extensions = require(`${process.cwd()}/web/js/extensions.json`);
const security = require('../cucumber/contracts/security.json')
const dateFormat = require('../cucumber/contracts/date-format.json')
const { readFileSync } = require('fs');
const path = require('path');
const htmlTemplate = readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8');
const translations = readFileSync(path.join(process.cwd(), './web/js/translation/en_US.js'), 'utf-8');
const userBuilder = new UserBuilder();
const localeBuilder = new LocaleBuilder();

module.exports = function (cucumber) {
  const { Before, After, Status } = cucumber;

  Before({ timeout: 10 * 1000 }, async function () {
    this.baseUrl = 'http://pim.com';
    this.browser = await puppeteer.launch({
      devtools: true,
      ignoreHTTPSErrors: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1920,1080'],
      headless: true,
      slowMo: 0,
      pipe: true,
      defaultViewport: {
        width: 1920,
        height: 1080
      }
    });

    this.page = await this.browser.newPage();
    await this.page.setRequestInterception(true);

    this.consoleLogs = [];

    this.page.on('console', message => {
      if (['error', 'warning'].includes(message.type())) {
        this.consoleLogs.push(message.text());
      }
    });

    this.page.on('request', request => {
      if (request.url() === `${this.baseUrl}/`) {
        return request.respond({
          contentType: 'text/html; charset=UTF-8',
          body: htmlTemplate,
        });
      }
      if (request.url().includes('/rest/user/')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(userBuilder.build())}`,
        });
      }

      if (request.url().includes('/js/extensions.json')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(extensions)}`,
        });
      }

      if (request.url().includes('/js/translation')) {
        return request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(translations)}`,
        });
      }

      if (request.url().includes('/security')) {
        return request.respond({
          contentType: 'application/json',
          body: JSON.stringify(security)
        })
      }

      if (request.url().includes('/configuration/locale')) {
        const en_US = localeBuilder.withCode('en_US').build();

        return request.respond({
          contentType: 'application/json',
          body: JSON.stringify([en_US])
        })
      }

      if (request.url().includes('localization/format/date')) {
        return request.respond({
          contentType: 'application/json',
          body: JSON.stringify(dateFormat)
        })
      }

      // Assets
      if (request.url().includes('/bundles/')) {
        try {
         const assetPath = request.url().split('/bundles/')[1];
         return request.respond({
           body: readFileSync(`${process.cwd()}/web/bundles/${assetPath}`)
         });
        } catch (e) {
          request.continue()
        }
      }
    });

    await this.page.goto(this.baseUrl);
    await this.page.addStyleTag({ content: readFileSync(`${process.cwd()}/web/css/pim.css`, 'utf-8') })
    await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
    await this.page.evaluate(async () => await require('pim/init')());
    await this.page.evaluate(async () => await require('pim/user-context').initialize());
    await this.page.evaluate(async () => await require('pim/date-context').initialize());
    await this.page.evaluate(async () => await require('pim/init-translator').fetch());
    await this.page.evaluate(async () => await require('oro/init-layout')());
  });

  After(async function (scenario) {
    this.consoleLogs = this.consoleLogs || [];
    if (Status.FAILED === scenario.result.status) {
      if (0 < this.consoleLogs.length) {
        const logMessages = this.consoleLogs.reduce((result, message) => `${result}\nError logged: ${message}`, '');

        await this.attach(logMessages, 'text/plain');
        console.log(logMessages);
      }
    }

    // if (!this.parameters.debug) {
    //   await this.page.close();
    //   await this.browser.close();
    // }
  });
};
