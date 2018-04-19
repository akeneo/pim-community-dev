import * as puppeteer from 'puppeteer';
import BaseView = require('pimenrich/js/view/base');

const random = process.env.RANDOM_LATENCY || true;
const maxRandomLatency: number = undefined !== process.env.MAX_RANDOM_LATENCY_MS ? parseInt(process.env.MAX_RANDOM_LATENCY_MS) : 1000;

export const answer = (methodToDelay: () => any, randomLatency = random, customMaxRandomLatency: number = maxRandomLatency) => {
    setTimeout(methodToDelay, (randomLatency ? Math.random() : 1) * customMaxRandomLatency);
};

export const answerJson = (request: puppeteer.Request, response: string, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
    answer(() => request.respond(json(response)), randomLatency, customMaxRandomLatency);
};

export const json = (body: string) => ({
    contentType: 'application/json',
    body: typeof body === 'string' ? body : JSON.stringify(body)
});

export const csvToArray = (csv: string, separator = ',') => {
    return csv.split(separator).map((value: string) => value.trim());
};

export const renderFormExtension = async (page: puppeteer.Page, extension: string, data: any) => {
    await page.evaluate((extensionName, volumes) => {
        const FormBuilder = require('pim/form-builder');

        return FormBuilder.build(extensionName).then((form: BaseView) => {
            form.setData(volumes);
            form.setElement(document.getElementById('app')).render();

            return form;
        });
    }, extension, data);
};
