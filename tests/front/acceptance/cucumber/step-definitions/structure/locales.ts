import * as cucumber from 'cucumber';
import * as puppeteer from 'puppeteer';

export default function() {
    const { Given } = cucumber;
    const createLocale = require('../../factory/locale');
    const  { answerJson, csvToArray } = require('../../tools');

    Given('the locales {string}', async function(csvLocaleCodes) {
        const locales = csvToArray(csvLocaleCodes).map((localeCode: string) => createLocale(localeCode));
        this.page.on('request', (request: puppeteer.Request) => {
            if (request.url().includes('/configuration/locale/rest')) {
                answerJson(request, locales);
            }
        });
    });
};
