const createUser = require('./factory/user');
const puppeteer = require('puppeteer');
const extensions = require(`${process.cwd()}/web/test_dist/extensions.json`);
const fs = require('fs');
const path = require('path');
const os = require('os');

module.exports = function(cucumber) {
    const {Before, After, Status} = cucumber;

    Before({timeout: 10 * 1000}, async function() {
        process.env.RANDOM = this.parameters.random || true;
        process.env.MAX_RANDOM_LATENCY_MS = this.parameters.maxLatency || 1000;

        this.baseUrl = 'http://pim.com/';
        this.browser = await puppeteer.launch({
            ignoreHTTPSErrors: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            headless: !this.parameters.debug,
            slowMo: 0
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
                    body: fs.readFileSync(process.cwd() + '/web/test_dist/index.html', 'utf-8')
                });
            }
            if (request.url().includes('/rest/user/')) {
                request.respond({
                    contentType: 'application/json',
                    body: `${JSON.stringify(createUser())}`
                });
            }

            if (request.url().includes('/form/extensions')) {
                request.respond({
                    contentType: 'application/json',
                    body: `${JSON.stringify(extensions)}`
                });
            }
        });

        await this.page.goto(this.baseUrl);
        await this.page.evaluate(async () => await require('pim/fetcher-registry').initialize());
        await this.page.evaluate(async () => await require('pim/user-context').initialize());
    });

    After(async function(scenario) {
        if (Status.FAILED === scenario.result.status) {
            const { uri, line } = scenario.sourceLocation;
            const fileName = `${uri}:${line}.png`;
            const folder = path.join(os.tmpdir(), 'js-acceptance');

            const filePath = path.join(folder, fileName.replace(/\//g, '__'));
            const imageBuffer = await this.page.screenshot({path: filePath});

            if (0 < this.consoleLogs.length) {
                const logMessages = this.consoleLogs.reduce(
                    (result, message) => `${result}\nError logged: ${message}`, ''
                );

                this.attach(logMessages, 'text/plain');
                console.log(logMessages);
            }

            if (!this.parameters.debug) {
                await this.page.close();
                await this.browser.close();
            }

            console.log(`Screenshot available at ${filePath}`);
            await this.attach(imageBuffer, 'image/png');
        }

        if (!this.parameters.debug) {
            await this.page.close();
            await this.browser.close();
        }
    });
};
