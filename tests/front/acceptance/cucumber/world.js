const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const os = require('os');
const {Before, After, Status} = require('cucumber');
const extensions = require(`${process.cwd()}/web/test_dist/extensions.json`);
const baseFile = fs.readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8');

Before({timeout: 10 * 1000}, async function() {
  process.env.RANDOM = this.parameters.random;
  process.env.MAX_RANDOM_LATENCY_MS = this.parameters.maxLatency;
  this.baseUrl = 'http://pim.com/';
  this.browser = await puppeteer.launch({
    ignoreHTTPSErrors: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
    headless: !this.parameters.debug,
    slowMo: 0,
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
    if (request.url() === this.baseUrl) {
      request.respond({
        contentType: 'text/html',
        body: fs.readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8'),
      });
    }
    if (request.url().includes('/rest/user/')) {
      request.respond({
        contentType: 'application/json',
        body: `{
  "username": "admin",
  "email": "admin@example.com",
  "namePrefix": null,
  "firstName": "John",
  "middleName": null,
  "lastName": "Doe",
  "nameSuffix": null,
  "birthday": null,
  "image": null,
  "lastLogin": 1518092814,
  "loginCount": 18,
  "catalogLocale": "en_US",
  "uiLocale": "en_US",
  "catalogScope": "ecommerce",
  "defaultTree": "master",
  "avatar": null,
  "meta": {
    "id": 1
  }
}`,
      });
    }

    if (request.url().includes('/form/extensions')) {
      request.respond({
        contentType: 'application/json',
        body: `${JSON.stringify(extensions)}`,
      });
    }
  });

  await this.page.goto(this.baseUrl);
  await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
  await this.page.evaluate(async () => await require('pim/user-context').initialize());
});

After(async function(scenario) {
  if (Status.FAILED === scenario.result.status) {
    const filePath = path.join(os.tmpdir(), 'scenario.png');
    const imageBuffer = await this.page.screenshot({path: filePath});
    if (0 < this.consoleLogs.length) {
      const logMessages = this.consoleLogs.reduce((result, message) => `${result}\nError logged: ${message}`, '');
      this.attach(logMessages, 'text/plain');
      console.log(logMessages);
    }

    if (!this.parameters.debug) {
      this.page.close();
      this.browser.close();
    }

    console.log(`Screenshot available at ${filePath}`, 'text/plain');

    return this.attach(imageBuffer, 'image/png');
  }

  if (!this.parameters.debug) {
    this.page.close();
    this.browser.close();
  }
});