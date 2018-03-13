module.exports = function(cucumber) {
    const {Given, Then} = cucumber;
    const assert = require('assert');
    const { createAssociationType } = require('../../fixtures');

    Given('the edit form for association type {string} is displayed', async function (string) {
        const associationType = createAssociationType(123, 'X_SELL', {
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

        await this.page.waitFor('.AknTitleContainer-title');
    });

    Then('the association type code should be {string}', async function (string) {
        const titleElement = await this.page.waitForSelector('#pim_enrich_association_type_form_code');
        const codeValue = await (await titleElement.getProperty('value')).jsonValue();
        assert.equal(codeValue.trim(), string);
    });
};
