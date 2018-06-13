const Sidebar = async(nodeElement, createElementDecorator, page) => {
    const getCollapseButton = async () => {
        return await nodeElement.$('.AknColumn-collapseButton');
    };

    const getTabsCode = async () => {
        return await page.evaluate((sidebar) => {
            const tabs = sidebar.querySelectorAll('.AknColumn-navigationLink');

            return Object.values(tabs).map((tab) => tab.dataset.tab);
        }, nodeElement);
    };

    const getActiveTabCode = async () => {
        const activeLink = nodeElement.$('.AknColumn-navigationLink--active');
        return await activeLink.getAttribute('data-tab');
    };

    return {getCollapseButton, getTabsCode, getActiveTabCode}
};

module.exports = Sidebar;
