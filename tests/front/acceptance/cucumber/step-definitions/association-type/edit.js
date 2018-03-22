module.exports = function(cucumber) {
    const { Given, Then } = cucumber;
    const assert = require('assert');
    const createAssociationType = require('../../factory/association-type');

    Given('the edit form for association type {string} is displayed', async function (string) {
        const associationType = createAssociationType('X_SELL', {
            en_US: 'Cross sell',
            fr_FR: 'Vente croisée'
        });

        await this.page.evaluate((associationType) => {
            const FormBuilder = require('pim/form-builder');

            return FormBuilder.build('pim-association-type-edit-form').then((form) => {
                form.setData(associationType);
                form.setElement(document.getElementById('app')).render();

                return form;
            });
        }, associationType);

        const titleElement = await this.page.waitFor('.AknTitleContainer-title');
        const pageTitle = await (await titleElement.getProperty('textContent')).jsonValue();
        assert.equal(pageTitle, string);
    });

    Then('the association type code should be {string}', async function (string) {
        const titleElement = await this.page.waitForSelector('#pim_enrich_association_type_form_code');
        const codeValue = await (await titleElement.getProperty('value')).jsonValue();
        assert.equal(codeValue.trim(), string);
    });
};
