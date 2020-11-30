const Assets = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    return true;
  };

  const hasAsset = async identifier => {
    await isLoaded();
    await page.waitFor(`.AknDefault-mainContent .AknGrid-bodyRow [data-identifier="${identifier}"]`);
    const asset = await nodeElement.$(`[data-identifier="${identifier}"]`);

    return asset !== null;
  };

  const isEmpty = async () => {
    try {
      await page.waitForSelector('.AknDefault-mainContent .AknGridContainer-noData', {timeout: 2000});
    } catch (e) {
      return false;
    }

    return true;
  };

  const getAssetLabel = async identifier => {
    const label = await nodeElement.$(`a[data-identifier="${identifier}"]`);
    const labelProperty = await label.getProperty('textContent');

    return await labelProperty.jsonValue();
  };

  const getAssetCompleteness = async identifier => {
    await page.waitFor(`tr[data-identifier="${identifier}"] .AknBadge`);
    const span = await nodeElement.$(`tr[data-identifier="${identifier}"] .AknBadge`);
    const completeness = await span.getProperty('textContent');

    return Number.parseInt((await completeness.jsonValue()).replace('%', ''));
  };

  const hasSuccessNotification = async () => {
    await page.waitForSelector('[role="success"]');

    return true;
  };

  const search = async searchInput => {
    const search = await page.waitFor('.AknFilterBox-search');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFilterBox-search').value = '');
    }, nodeElement);

    await search.type(searchInput);
  };

  const filterOption = async (attributeCode, options) => {
    const containerSelector = `.AknFilterBox-filter[data-attribute="${attributeCode}"]`;
    await page.waitForSelector(containerSelector);

    const filterSelector = `.AknFilterBox-filter[data-attribute="${attributeCode}"] .AknFilterBox-filterLabel`;
    await page.waitForSelector(filterSelector);
    const filterButton = await nodeElement.$(filterSelector);
    await filterButton.click();

    const selectSelector = `.AknFilterBox-filter[data-attribute="${attributeCode}"] select.asset-option-selector`;
    await page.waitForSelector(selectSelector);
    const optionSelect = await nodeElement.$(selectSelector);

    for (const option of options) {
      const optionElement = await optionSelect.$(`option[value="${option}"]`);
      await optionElement.click();
    }
  };

  const filterLink = async (attributeCode, assetCode) => {
    const containerSelector = `.AknFilterBox-filter[data-attribute="${attributeCode}"]`;
    await page.waitForSelector(containerSelector);
    const container = await nodeElement.$(containerSelector);

    const filterSelector = `.AknFilterBox-filter[data-attribute="${attributeCode}"] .AknFilterBox-filterLabel`;
    await page.waitForSelector(filterSelector);
    const filterButton = await nodeElement.$(filterSelector);
    await filterButton.click();

    const assetSelectorInput = `.AknFilterBox-filter[data-attribute="${attributeCode}"] .asset-selector`;
    await page.waitForSelector(assetSelectorInput);
    const assetInput = await container.$(assetSelectorInput);
    await assetInput.type(assetCode);
  };

  const hasErrorNotification = async () => {
    await page.waitForSelector('[role="alert"]');

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
    hasAsset,
    isLoaded,
    isEmpty,
    getAssetLabel,
    getAssetCompleteness,
    hasSuccessNotification,
    hasErrorNotification,
    search,
    completeFilter,
    filterOption,
    filterLink,
  };
};

module.exports = Assets;
