const Asset = async (nodeElement, createElementDecorator, page) => {
  const remove = async () => {
    const assetCode = await getAssetCode();

    const assetSelector = `[data-asset="${assetCode}"]`;
    const removeButtonSelector = `${assetSelector} [data-remove="${assetCode}"]`;
    await page.hover(assetSelector);
    await page.waitFor(removeButtonSelector);
    const removeButton = await nodeElement.$(removeButtonSelector);
    await removeButton.click();
  };

  const move = async direction => {
    const assetCode = await getAssetCode();

    const assetSelector = `[data-asset="${assetCode}"]`;
    const moveButtonSelector = `${assetSelector} [data-move-${direction}="${assetCode}"]`;

    await page.hover(assetSelector);
    await page.waitFor(moveButtonSelector);
    const moveButton = await nodeElement.$(moveButtonSelector);
    await moveButton.click();
  };

  const getAssetCode = async () => {
    return await page.evaluate(nodeElement => nodeElement.dataset.asset, nodeElement);
  };

  return {remove, move, getAssetCode};
};

module.exports = Asset;
