const Properties = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent');

    return true;
  };

  const getTabCode = async () => {
    return await page.evaluate(properties => {
      return properties.dataset.tab;
    }, nodeElement);
  };

  const getIdentifier = async () => {
    const identifier = await nodeElement.$('.AknTextField[name="identifier"]');
    const identifierProperty = await identifier.getProperty('value');

    return await identifierProperty.jsonValue();
  };

  const getLabel = async () => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    const labelProperty = await label.getProperty('value');

    return await labelProperty.jsonValue();
  };

  const labelIsReadOnly = async () => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    const labelProperty = await label.getProperty('readOnly');

    debugger;
  };

  const setLabel = async value => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknTextField[name="label"]').value = '');
    }, nodeElement);

    await label.type(value);
  };

  return {isLoaded, getIdentifier, getLabel, setLabel, getTabCode, labelIsReadOnly};
};

module.exports = Properties;
