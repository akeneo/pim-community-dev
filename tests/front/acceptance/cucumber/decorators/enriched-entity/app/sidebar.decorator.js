const Sidebar = async(nodeElement, createElementDecorator, page) => {
    const collapse = async () => {
        // As the button doesn't have any size, we need to make it clickable by giving him a size
        await page.evaluate((sidebar) => {
            const button = sidebar.querySelector('.AknColumn-collapseButton');

            button.style.width = '100px';
            button.style.height = '100px';
        }, nodeElement);

        const button = await nodeElement.$('.AknColumn-collapseButton');
        await button.click();
    };

    const isCollapsed = async () => {
        await page.waitFor('.AknColumn--collapsed');

        return true;
    };

    const getTabsCode = async () => {
        return await page.evaluate((sidebar) => {
            const tabs = sidebar.querySelectorAll('.AknColumn-navigationLink');

            return Object.values(tabs).map((tab) => tab.dataset.tab);
        }, nodeElement);
    };

    const getActiveTabCode = async () => {
        return await page.evaluate((sidebar) => {
            return sidebar.querySelector('.AknColumn-navigationLink--active').dataset.tab;
        }, nodeElement);
    };

    return {collapse, getTabsCode, getActiveTabCode, isCollapsed}
};

module.exports = Sidebar;
