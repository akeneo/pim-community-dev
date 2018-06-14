const Grid = async(nodeElement, createElementDecorator, page) => {
    const isLoaded = async () => {
      await page.waitFor('.AknGridContainer');

      return true;
    };

    const getRowsAfterLoading = async () => {
        await page.waitForSelector('.AknGrid-bodyRow:not(.AknLoadingPlaceHolder)');
        return await nodeElement.$$('.AknGrid-bodyRow:not(.AknLoadingPlaceHolder)');
    };

    const getRows = async () => {
      return await page.$$('.AknGrid-bodyRow:not(.AknLoadingPlaceHolder)');
    };

    const getTitle = async () => {
        const title = await page.waitForSelector('.AknTitleContainer-title');
        const titleProperty = await title.getProperty('textContent');

        return await titleProperty.jsonValue();
    };

    const hasRow = async (identifier) => {
        await page.waitForSelector(`.AknGrid-bodyRow[data-identifier="${identifier}"]`);

        return true;
    };

    return {isLoaded, getRowsAfterLoading, getRows, getTitle, hasRow};
};

module.exports = Grid;
