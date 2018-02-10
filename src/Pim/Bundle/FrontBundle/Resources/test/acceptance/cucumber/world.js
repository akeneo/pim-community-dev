const puppeteer = require('puppeteer');
const fs = require('fs');
const {Before, After} = require('cucumber');

const DEBUG = process.env.DEBUG === '0';
process.env.RANDOM = true;
const baseFile = fs.readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8');

Before(async function() {
  this.baseUrl = 'http://pim.com/';
  this.browser = await puppeteer.launch({
    ignoreHTTPSErrors: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
    headless: true,
    slowMo: DEBUG ? 250 : 0,
  });
  this.page = await this.browser.newPage();
  await this.page.setRequestInterception(true);
  this.page.on('request', interceptedRequest => {
    if (interceptedRequest.url() === this.baseUrl) {
      interceptedRequest.respond({
        contentType: 'text/html',
        body: baseFile,
      });
    }
    if (interceptedRequest.url().includes('/rest/user/')) {
      interceptedRequest.respond({
        contentType: 'application/json',
        body:
          '{"username":"admin","email":"admin@example.com","namePrefix":null,"firstName":"John","middleName":null,"lastName":"Doe","nameSuffix":null,"birthday":null,"image":null,"lastLogin":1518092814,"loginCount":18,"catalogLocale":"en_US","uiLocale":"en_US","catalogScope":"ecommerce","defaultTree":"master","avatar":null,"meta":{"id":1}}',
      });
    }
  });
  await this.page.goto(this.baseUrl);
  await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
  await this.page.evaluate(async () => await require('pim/user-context').initialize());
});

After(async function() {
  this.page.close();
  this.browser.close();
});
