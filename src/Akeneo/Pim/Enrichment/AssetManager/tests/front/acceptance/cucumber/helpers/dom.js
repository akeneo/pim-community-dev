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

module.exports = getElementSelector;
