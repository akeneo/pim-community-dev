import * as cucumber from 'cucumber';
import * as assert from 'assert';
import { renderFormExtension } from '../../tools';

export default async function() {
    const { Given, Then, When } = cucumber;

    let data = {
        average_max_attributes_per_family: {
            value: { average: 7, max: 10 },
            has_warning: false,
            type: 'average_max'
        }
    };

    Given('a family with {int} attributes', (int: number) => assert(int));

    Given('the limit of the number of attributes per family is set to {int}', int => {
        if (data.average_max_attributes_per_family.value.max > int) {
            data.average_max_attributes_per_family.has_warning = true;
        }

        assert(true);
    });

    When('the administrator user asks for the catalog volume monitoring report', async function () {
        await renderFormExtension(this.page, 'pim-catalog-volume-index', data);
        const titleElement = await this.page.waitForSelector('.AknTitleContainer-title');
        const pageTitle = await (await titleElement.getProperty('textContent')).jsonValue();
        assert.equal(pageTitle.trim(), 'Catalog volume monitoring');
    });

    Then('the report returns that the average number of attributes per family is {int}', async function (int) {
        const meanSelector = '[data-field="average_max_attributes_per_family"] span:nth-child(1) div';
        const valueElement = await this.page.waitForSelector(meanSelector);
        const value = await (await valueElement.getProperty('textContent')).jsonValue();
        assert.equal(value, int);
    });

    Then('the report returns that the maximum number of attributes per family is {int}', async function (int) {
        const meanSelector = '[data-field="average_max_attributes_per_family"] span:nth-child(2) div';
        const valueElement = await this.page.waitForSelector(meanSelector);
        const value = await (await valueElement.getProperty('textContent')).jsonValue();
        assert.equal(value, int);
    });

    Then('the report warns the users that the number of attributes per family is high', async function () {
        const warningSelector = '[data-field="average_max_attributes_per_family"] + .AknCatalogVolume-warning';
        const valueElement = await this.page.waitForSelector(warningSelector);
        const value = await (await valueElement.getProperty('textContent')).jsonValue();
        assert.equal(
            value.trim(),
            'Wow! You hit a record with this axis! Don\'t hesitate to contact us if you need any help with this kind of volume.'
        );
    });
};
