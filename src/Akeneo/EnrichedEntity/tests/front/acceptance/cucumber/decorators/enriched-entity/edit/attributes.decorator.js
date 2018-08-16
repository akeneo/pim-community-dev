const Attributes = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent .AknSubsection .AknFieldContainer');

    return true;
  };

  const hasAttribute = async (code, type) => {
    await isLoaded();
    const attribute = await nodeElement.$(`.AknFieldContainer[data-identifier="${code}"][data-type="${type}"]`);

    return attribute !== null;
  };

  return {hasAttribute, isLoaded};
};

module.exports = Attributes;
