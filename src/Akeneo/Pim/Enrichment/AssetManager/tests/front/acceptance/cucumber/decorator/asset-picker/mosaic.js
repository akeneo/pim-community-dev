const Mosaic = async (nodeElement, createElementDecorator, page) => {
  const select = async assetCode => {
    const unselectedAssetSelector = `[data-asset="${assetCode}"][data-selected="false"]`;
    await page.waitForSelector(unselectedAssetSelector);

    const assetCard = await nodeElement.$(unselectedAssetSelector);
    await assetCard.click();

    const assetShouldBeSelected = `[data-asset="${assetCode}"][data-selected="true"]`;
    await page.waitForSelector(assetShouldBeSelected);
  };

  return {select};
};

module.exports = Mosaic;
