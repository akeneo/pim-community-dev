const Properties = async(nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent');

    return true;
  };

    const getTabCode = async () => {
        return await page.evaluate((properties) => {
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

    return {isLoaded, getIdentifier, getLabel, getTabCode};
};

module.exports = Properties;
