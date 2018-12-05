const Records = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    return true;
  };

  const hasRecord = async identifier => {
    await isLoaded();
    await page.waitFor(`.AknDefault-mainContent .AknGrid-bodyRow [data-identifier="${identifier}"]`);
    const record = await nodeElement.$(`[data-identifier="${identifier}"]`);

    return record !== null;
  };

  const isEmpty = async () => {
    try {
      await page.waitForSelector('.AknDefault-mainContent .AknGridContainer-noData', {timeout: 2000});
    } catch (e) {
      return false;
    }

    return true;
  };

  const getRecordLabel = async identifier => {
    const label = await nodeElement.$(`a[data-identifier="${identifier}"]`);
    const labelProperty = await label.getProperty('textContent');

    return await labelProperty.jsonValue();
  };

  const hasSuccessNotification = async () => {
    await page.waitForSelector('.AknFlash--success');

    return true;
  };

  const search = async searchInput => {
    const search = await page.waitFor('.AknFilterBox-search');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFilterBox-search').value = '');
    }, nodeElement);

    await search.type(searchInput);
  };

  const hasErrorNotification = async () => {
    await page.waitForSelector('.AknFlash--error');

    return true;
  };

  const completeFilter = async value => {
    await page.waitForSelector('.complete-filter.AknDropdown');
    const openButton = await nodeElement.$('.complete-filter.AknDropdown .AknActionButton[data-identifier]');
    await openButton.click();
    await page.waitForSelector('.complete-filter.AknDropdown .AknDropdown-menuLink');
    const valueButton = await nodeElement.$(`.AknDropdown-menuLink[data-identifier="${value}"]`);
    await valueButton.click();

    await page.waitForSelector(`.AknActionButton-highlight[data-identifier="${value}"]`);
  };

  return {
    hasRecord,
    isLoaded,
    isEmpty,
    getRecordLabel,
    hasSuccessNotification,
    hasErrorNotification,
    search,
    completeFilter
  };
};

module.exports = Records;
