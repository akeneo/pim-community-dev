const Edit = async (nodeElement, createElementDecorator, page) => {
  const productExists = async productIdentifier => {
    await page.waitForSelector('.AknGrid-bodyRow');
    const productRow = await nodeElement.$(`.AknGrid-bodyRow[data-identifier="${productIdentifier}"]`);

    return null !== productRow;
  };

  const noLinkedProduct = async () => {
    await page.waitForSelector('.AknGridContainer-noDataImage--product');
    const noProductMessage = await nodeElement.$('.AknGridContainer-noDataImage--product');

    return null !== noProductMessage;
  };

  const noLinkedAttribute = async () => {
    await page.waitForSelector('.AknGridContainer-noDataImage--reference-entity');
    const noAtttributeMessage = await nodeElement.$('.AknGridContainer-noDataImage--reference-entity');

    return null !== noAtttributeMessage;
  };

  return {productExists, noLinkedProduct, noLinkedAttribute};
};

module.exports = Edit;
