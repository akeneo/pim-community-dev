module.exports = async function(cucumber) {
    const { Given, Then, When } = cucumber;
    const assert = require('assert');

    // @TODO - to complete in PIM-7211
    Given('a family with {int} attributes', async function (int) {
        assert(int);
    });

    Given('the limit of the number of attributes per family is set to {int}', async function (int) {
        assert(int);
    });

    When('the administrator user asks for the catalog volume monitoring report', async function () {
        const data = {
            count_product_values: { value: 36867028, has_warning: false, type: 'count'},
            average_max_product_values_average: { value: { average: 10, max: 20 }, has_warning: false, type: 'average_max' },
            count_products: { value: 120000, has_warning: false, type: 'count'},
            average_max_attributes_per_family: { value: {average: 75, max: 75 }, has_warning: false, type: 'average_max'},
            count_channels: { value: 3, has_warning: false, type: 'count'},
            count_locales: { value: 4, has_warning: false, type: 'count'},
            count_scopable_attributes:{ value: 2, has_warning: false, type: 'count'},
            count_localizable_and_scopable_attributes: { value: 4, has_warning: false, type: 'count'},
            count_localizable_attributes: { value: 8, has_warning: false, type: 'count'},
            count_families: { value: 24, has_warning: false, type: 'count'},
            count_attributes: { value: 120, has_warning: false, type: 'count'},
            average_max_options_per_attribute: { value: { average: 10, max: 20 }, has_warning: false, type: 'average_max'},
            count_categories: { value: 10001, has_warning: true, type: 'count'},
            count_category_trees: { value: 3, has_warning: false, type: 'count'},
            count_variant_products: { value: 120000, has_warning: false, type: 'count'},
            count_product_models: { value: 21000, has_warning: false, type: 'count'}
        };

        await this.page.evaluate((volumes) => {
            const FormBuilder = require('pim/form-builder');

            return FormBuilder.build('pim-catalog-volume-index').then((form) => {
                form.setData(volumes);
                form.setElement(document.getElementById('app')).render();

                return form;
            });
        }, data);

        const titleElement = await this.page.waitForSelector('.AknTitleContainer-title');
        const pageTitle = await (await titleElement.getProperty('textContent')).jsonValue();
        assert.equal(pageTitle.trim(), 'Catalog volume monitoring');

        // Only check that the page loads

        // Load the catalog volume monitoring view and render the axes
    });

    Then('the report returns that the average number of attributes per family is {int}', async function (int) {
        // Get the average from before ?
        assert(int);
    });

    Then('the report returns that the maximum number of attributes per family is {int}', function (int) {
        assert(int);
    });

    Then('the report warns the users that the number of attributes per family is high', async function () {
        // Check that it's rendered on the page and it has a warning
        assert(true);
    });
};
