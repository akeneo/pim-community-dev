const AssetPicker = async (nodeElement, createElementDecorator, page) => {
  const searchBarSelector = '[data-container="search-bar"]';
  const basketSelector = '[data-container="basket"]';
  const filterCollectitonSelector = '[data-container="filter-collection"]';
  const mosaicSelector = '[data-container="mosaic"]';

  const getElement = createElementDecorator({
    'Search bar': {
      selector: searchBarSelector,
      decorator: require('./search-bar'),
    },
    'Filter collection': {
      selector: filterCollectitonSelector,
      decorator: require('./filter-collection'),
    },
    Mosaic: {
      selector: mosaicSelector,
      decorator: require('./mosaic'),
    },
    Basket: {
      selector: basketSelector,
      decorator: require('./basket'),
    },
  });

  const getSearchBar = async () => {
    await page.waitFor(searchBarSelector);

    return await getElement(page, 'Search bar');
  };

  const getFilterCollection = async () => {
    await page.waitFor(filterCollectitonSelector);

    return await getElement(page, 'Filter collection');
  };

  const getMosaic = async () => {
    await page.waitFor(mosaicSelector);

    return await getElement(page, 'Mosaic');
  };

  const getBasket = async () => {
    await page.waitFor(basketSelector);

    return await getElement(page, 'Basket');
  };

  const confirmSelection = async () => {
    const confirmButtonSelector = '[title="Confirm"]';

    await page.waitFor(confirmButtonSelector);
    const confirmSelectionButton = await nodeElement.$(confirmButtonSelector);
    await confirmSelectionButton.click();
  };

  return {getSearchBar, getFilterCollection, getMosaic, getBasket, confirmSelection};
};

module.exports = AssetPicker;
