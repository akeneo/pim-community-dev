const Sidebar = require('../../decorators/enriched-entity/app/sidebar.decorator');

const {
    decorators: { createElementDecorator }
} = require('../../test-helpers.js');

module.exports = async function (cucumber) {
    const {Given, Then, When} = cucumber;
    const assert = require('assert');

    const config = {
        'Sidebar': {
            selector: '.AknColumn',
            decorator: Sidebar
        }
    };

    const getElement = createElementDecorator(config);

    Given('the following configured tabs:', async function(tabs) {
        this.expectedTabs = await tabs.hashes().reduce((previous, current) => {
            return [...previous, current.code];
        }, []);
    });

    When('the user tries to collapse the sidebar', async function () {
        const sidebar = await (await getElement(this.page, 'Sidebar'));
        await sidebar.collapse();
    });

    Then('the user should see the sidebar collapsed', async function () {
        const sidebar = await (await getElement(this.page, 'Sidebar'));
        const isCollapsed = await sidebar.isCollapsed();

        assert.strictEqual(isCollapsed, true);
    });

    Then('the user should see the sidebar with the configured tabs', async function () {
        const sidebar = await (await getElement(this.page, 'Sidebar'));
        const values = await sidebar.getTabsCode();

        assert.deepStrictEqual(values, this.expectedTabs);
    });

    Then('the user should see the active tab view', async function () {
        const sidebar = await (await getElement(this.page, 'Sidebar'));
        const activeTab = await sidebar.getActiveTabCode();

        await this.page.waitFor(`[data-tab=${activeTab}]`);
    });
};
