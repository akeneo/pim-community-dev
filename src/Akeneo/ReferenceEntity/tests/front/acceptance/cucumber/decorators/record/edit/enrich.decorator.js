const Enrich = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent');

    return true;
  };

  const getTabCode = async () => {
    return await page.evaluate(properties => {
      return properties.dataset.tab;
    }, nodeElement);
  };

  const getLabel = async () => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    const labelProperty = await label.getProperty('value');

    return await labelProperty.jsonValue();
  };

  const setLabel = async value => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknTextField[name="label"]').value = '');
    }, nodeElement);

    await label.type(value);
  };

  return {isLoaded, getLabel, setLabel, getTabCode};
};

module.exports = Enrich;
