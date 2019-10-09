const getElementSelector = async (page, element) => {
  const selector = await page.evaluate(element => {
    const getSelector = nodeElement => {
      const hasClass = '' !== nodeElement.className;
      const currentClass = hasClass ? `.${nodeElement.className.split(' ').join('.')}` : '';
      const tagName = nodeElement.tagName.toLowerCase();

      return null !== nodeElement.parentElement
        ? `${getSelector(nodeElement.parentElement)} > ${tagName}${currentClass}`
        : `${tagName}${currentClass}`;
    };

    return getSelector(element);
  }, element);

  return selector;
};

const SearchBar = async (nodeElement, createElementDecorator, page) => {
  const search = async value => {
    const inputSelector = await getElementSelector(page, nodeElement);
    await page.type(inputSelector, value);
  };

  return {search};
};

module.exports = SearchBar;
