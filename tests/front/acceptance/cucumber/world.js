const UserBuilder = require('../../common/builder/user');
const puppeteer = require('puppeteer');
const extensions = require(`${process.cwd()}/web/js/extensions.json`);
const fs = require('fs');
const path = require('path');

const userBuilder = new UserBuilder();

module.exports = function(cucumber) {
    const {Before, After, Status} = cucumber;

    Before({timeout: 10 * 1000}, async function() {
        console.log('Step 1: Before');
        this.baseUrl = 'http://pim.com/';
        this.browser = await puppeteer.launch({
            ignoreHTTPSErrors: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            headless: !this.parameters.debug,
            slowMo: 0
        });

        console.log('Step 2: Browser');
        this.page = await this.browser.newPage();
        await this.page.setRequestInterception(true);

        console.log('Step 3: Load browser and begin interception');
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
                    body: fs.readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8')
                });
            }
            if (request.url().includes('/rest/user/')) {
                request.respond({
                    contentType: 'application/json',
                    body: `${JSON.stringify(userBuilder.build())}`
                });
            }

            if (request.url().includes('/form/extensions')) {
                request.respond({
                    contentType: 'application/json',
                    body: `${JSON.stringify(extensions)}`
                });
            }

            if (request.url().includes('/js/translation')) {
                const language = path.basename(request.url());
                const languageContents = fs.readFileSync(path.join(process.cwd(), `./web/js/translation/${language}`), 'utf-8');

                request.respond({
                    contentType: 'application/json',
                    body: `${JSON.stringify(languageContents)}`
                });
            }
        });

        console.log('Step 4: Go to the pim');
        await this.page.goto(this.baseUrl);
        console.log('Step 5: Set up the page');
        await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
        await this.page.evaluate(async () => await require('pim/user-context').initialize());
        await this.page.evaluate(async () => await require('pim/init-translator').fetch());
        console.log('Done');
        console.log('-------');
    });

    After(async function(scenario) {
        if (Status.FAILED === scenario.result.status) {
            if (0 < this.consoleLogs.length) {
                const logMessages = this.consoleLogs.reduce(
                    (result, message) => `${result}\nError logged: ${message}`, ''
                );

                this.attach(logMessages, 'text/plain');
                console.log(logMessages);
            }
        }

        if (!this.parameters.debug) {
            await this.page.close();
            await this.browser.close();
        }
    });
};
