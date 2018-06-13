const Sidebar = async(nodeElement) => {
    const getCollapseButton = async () => {
        return await nodeElement.$('.AknColumn-collapseButton');
    };

    const getTabsCode = async () => {
        const tabs = await nodeElement.$$('.AknColumn-navigationLink');

        const codes = tabs.map(async element => (await element.getProperty('data-tab')).jsonValue());

        const realCodes = await Promise.all(codes);
        console.log(realCodes);
        // return await Promise.all(
        //     (await nodeElement.$$('.AknColumn-navigationLink'))
        //         .map(element => element.getProperty('data-tab'))
        // );
    };

    const getActiveTabCode = async () => {
        const activeLink = nodeElement.$('.AknColumn-navigationLink--active');
        return await activeLink.getAttribute('data-tab');
    };

    return {getCollapseButton, getTabsCode, getActiveTabCode}
};

module.exports = Sidebar;
