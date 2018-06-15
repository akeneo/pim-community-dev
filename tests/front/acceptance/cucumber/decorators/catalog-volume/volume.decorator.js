const Volume = async (nodeElement) => {
  const getType = async () => {
    const element = await nodeElement.$('[data-field]');
    const dataset = await (await element.getProperty('dataset'));

    return await (await dataset.getProperty('field')).jsonValue();
  };

  const getValue = async () => {
    const value = await nodeElement.$('.AknCatalogVolume-value');
    const mean = await value.$('.AknCatalogVolume-value span:nth-child(1) div');
    const max = await value.$('.AknCatalogVolume-value span:nth-child(2) div');

    if (mean || max) {
      return {
        mean: await (await mean.getProperty('textContent')).jsonValue(),
        max: await (await max.getProperty('textContent')).jsonValue()
      };
    }

    const rawValue = await (await value.getProperty('textContent')).jsonValue()

    return rawValue.trim();
  };

  const getWarning = async () => {
    const warningElement = await nodeElement.$('.AknCatalogVolume-warning');
    const warning = await (await warningElement.getProperty('textContent')).jsonValue();

    return warning.trim()
  };

  return { getType, getValue, getWarning };
};

module.exports = Volume;
