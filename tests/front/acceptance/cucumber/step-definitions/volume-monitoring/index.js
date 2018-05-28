module.exports = async function(cucumber) {
    const { Given, Then, When, Before } = cucumber;
    const assert = require('assert');
    const path = require('path');
    const {
        decorators: { createElementDecorator, Report },
        tools: { renderView }
    } = require('../../test-helpers.js');

    const config = {
        'Catalog volume report':  {
            selector: '.AknDefault-mainContent',
            decorator: Report
        }
    };

    let data = {
        count_asset_categories: {
            value: 5,
            has_warning: false,
            type: 'count'
        }
    };

    Before(async function() {
        this.getElement = createElementDecorator(config, this.page);
    });

    Given('a catalog with {int} asset categories', async function(int) {
        await renderView(this.page, 'pim-catalog-volume-index', data);
        assert(int)
    });

    Then('the report returns that the number of asset categories is {int}', async function (int) {
        const report = await (await this.getElement('Catalog volume report'));
        const volume = await report.getVolumeByType('count_asset_categories');
        const value = await volume.getValue();
        assert.equal(value, int);
    });
};
