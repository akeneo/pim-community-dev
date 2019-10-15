const UserBuilder = require('../../common/builder/user');
const puppeteer = require('puppeteer');
const extensions = require(`${process.cwd()}/public/js/extensions.json`);
const fs = require('fs');
const path = require('path');
const htmlTemplate = fs.readFileSync(process.cwd() + '/public/test_dist/index.html', 'utf-8');
const translations = fs.readFileSync(path.join(process.cwd(), './public/js/translation/en_US.js'), 'utf-8');
const userBuilder = new UserBuilder();
module.exports = function(cucumber) {
  const {Before, After, Status} = cucumber;

  Before({timeout: 10 * 1000}, async function() {
    this.baseUrl = 'http://pim.com';
    this.browser = await puppeteer.launch({
      devtools: this.parameters.debug,
      ignoreHTTPSErrors: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
      headless: !this.parameters.debug,
      slowMo: 0,
      pipe: true,
    });

    this.page = await this.browser.newPage();
    await this.page.setRequestInterception(true);

    this.consoleLogs = [];

    this.page.on('console', message => {
      if (['error', 'warning'].includes(message.type())) {
        this.consoleLogs.push(message.text());
      }
    });
    this.page.setMaxListeners(20);
    this.page.on('request', request => {
      if (request.url() === `${this.baseUrl}/`) {
        request.respond({
          contentType: 'text/html; charset=UTF-8',
          body: htmlTemplate,
        });
      }
      if (request.url().includes('/rest/user/')) {
        request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(userBuilder.build())}`,
        });
      }

      if (request.url().includes('/js/extensions.json')) {
        request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(extensions)}`,
        });
      }

      if (request.url().includes('/js/translation')) {
        request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify(translations)}`,
        });
      }
    });

    await this.page.goto(this.baseUrl);
    await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
    await this.page.evaluate(async () => await require('pim/user-context').initialize());
    await this.page.evaluate(async () => await require('pim/init-translator').fetch());
  });

  After(async function(scenario) {
    this.consoleLogs = this.consoleLogs || [];
    if (Status.FAILED === scenario.result.status) {
      if (0 < this.consoleLogs.length) {
        const logMessages = this.consoleLogs.reduce((result, message) => `${result}\nError logged: ${message}`, '');

        await this.attach(logMessages, 'text/plain');
        console.log(logMessages);
      }
    }

    if (!this.parameters.debug) {
      await this.page.close();
      await this.browser.close();
    }
  });
};
