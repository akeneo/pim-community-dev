const Asset = require('./asset');

const AssetCollection = async (nodeElement, createElementDecorator, page) => {
  const isEmpty = async () => {
    await page.waitFor('[title="Sorry, there is no asset in this collection."]');
    const hasEmptyNotice = await nodeElement.$('[title="Sorry, there is no asset in this collection."]');

    return null !== hasEmptyNotice;
  };

  const getAssetCodes = async () => {
    await page.waitFor('[data-asset]');

    const assetCodes = await page.evaluate(assetCollection => {
      const assets = assetCollection.querySelectorAll('[data-asset]');

      return [...assets].map(asset => asset.dataset.asset);
    }, nodeElement);

    return assetCodes;
  };

  const getAsset = async assetCode => {
    const getAsset = createElementDecorator({
      [assetCode]: {
        selector: `[data-asset="${assetCode}"]`,
        decorator: Asset,
      },
    });
    await page.waitFor(`[data-asset="${assetCode}"]`);

    return await getAsset(page, assetCode);
  };

  const getAttributeCode = async () => {
    return await page.evaluate(nodeElement => nodeElement.dataset.attribute, nodeElement);
  };

  const removeAll = async () => {
    const attributeCode = await getAttributeCode();
    await page.waitFor(`[data-attribute="${attributeCode}"]`);
    await page.waitFor('button[title="Open other actions"]');
    const moreButton = await nodeElement.$('button[title="Open other actions"]');

    moreButton.click();
    await page.waitFor('li[title="Remove all assets"]');
    const removeAllButton = await nodeElement.$('li[title="Remove all assets"]');

    removeAllButton.click();
  };

  return {getAssetCodes, getAsset, removeAll, isEmpty};
};

module.exports = AssetCollection;
