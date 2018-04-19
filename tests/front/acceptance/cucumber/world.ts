import * as puppeteer from 'puppeteer';
import * as cucumber from 'cucumber';
import * as fs from 'fs';
import * as path from 'path';
import { createUser } from './factory/user';

const extensions = require(`${process.cwd()}/web/js/extensions.json`);

const World: cucumber.World = function() {
    const { Before, After, Status } = cucumber;

    Before({timeout: 10 * 1000}, async function() {

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
        this.page.on('console', (message: puppeteer.ConsoleMessage) => {
            if (['error', 'warning'].includes(message.type())) {
                this.consoleLogs.push(message.text());
            }
        });

        this.page.on('request', (request: puppeteer.Request) => {
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

            if (request.url().includes('/js/translation')) {
                const language = path.basename(request.url());
                const languageContents = fs.readFileSync(path.join(process.cwd(), `./web/js/translation/${language}`), 'utf-8');

                request.respond({
                    contentType: 'application/json',
                    body: `${JSON.stringify(languageContents)}`
                });
            }
        });

        await this.page.goto(this.baseUrl);
        await this.page.evaluate(`require('pim/fetcher-registry').initialize()`);
        await this.page.evaluate(`require('pim/user-context').initialize()`);
        await this.page.evaluate(`require('pim/init-translator').fetch()`);
    });

    After(async function(scenario: cucumber.HookScenarioResult) {
        if (Status.FAILED === scenario.result.status) {
            if (0 < this.consoleLogs.length) {
                const logMessages = this.consoleLogs.reduce(
                    (result: string, message: string) => `${result}\nError logged: ${message}`, ''
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
}

export default {
    World: World
}
