const getElementSelector = require('../../helpers/dom');

const Mosaic = async (nodeElement, createElementDecorator, page) => {
  const select = async assetCode => {
    const mosaicSelector = await getElementSelector(page, nodeElement);
    const unselectedAssetSelector = `[data-asset="${assetCode}"][data-selected="false"] [role="checkbox"]`;
    await page.waitForSelector(`${mosaicSelector} ${unselectedAssetSelector}`);

    const assetCard = await nodeElement.$(unselectedAssetSelector);
    await assetCard.click();

    const assetShouldBeSelected = `${mosaicSelector} [data-asset="${assetCode}"][data-selected="true"]`;
    await page.waitForSelector(assetShouldBeSelected);
  };

  return {select};
};

module.exports = Mosaic;
