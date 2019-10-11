const getElementSelector = require('../../helpers/dom');

const SearchBar = async (nodeElement, createElementDecorator, page) => {
  const search = async value => {
    const inputSelector = await getElementSelector(page, nodeElement);
    await page.type(inputSelector, value);
  };

  return {search};
};

module.exports = SearchBar;
