const Image = async (nodeElement, createElementDecorator, page) => {
  const getCode = async () => {
    return await page.evaluate(el => {
      return el.getAttribute('alt');
    }, nodeElement);
  };

  return {getCode};
};

module.exports = Image;
