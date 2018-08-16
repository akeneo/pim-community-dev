const Attributes = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent .AknSubsection');

    return true;
  };

  const hasAttribute = async (code, type) => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknSubsection .AknFieldContainer');
    const attribute = await nodeElement.$(`.AknFieldContainer[data-identifier="${code}"][data-type="${type}"]`);

    return attribute !== null;
  };

  const isEmpty = async () => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknSubsection .AknGridContainer-noData');

    return true;
  };

  return {hasAttribute, isLoaded, isEmpty};
};

module.exports = Attributes;
