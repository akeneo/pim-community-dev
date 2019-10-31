const getElementSelector = require('../../helpers/dom');

const FilterCollection = async (nodeElement, createElementDecorator, page) => {
  const filter = async (attribute, value) => {
    const filterCollectionSelector = await getElementSelector(page, nodeElement);
    const filterSelector = `[data-attribute="${attribute}"] .AknFilterBox-filterCriteria`;
    await page.waitForSelector(`${filterCollectionSelector} ${filterSelector}`);
    const filter = await nodeElement.$(filterSelector);
    await filter.click();

    const valueSelector = `option[value="${value}"]`;
    await page.waitForSelector(`${filterCollectionSelector} ${valueSelector}`);
    const option = await nodeElement.$(valueSelector);
    await option.click();
  };

  return {filter};
};

module.exports = FilterCollection;
