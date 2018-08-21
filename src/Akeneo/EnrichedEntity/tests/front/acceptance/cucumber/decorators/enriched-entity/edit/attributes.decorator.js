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

  const remove = async () => {
    await page.evaluate(attributes => {
      const button = attributes.querySelector('.AknFieldContainer .AknIconButton--trash');

      button.style.width = '20px';
      button.style.height = '20px';
    }, nodeElement);

    page.on('dialog', async dialog => {
      await dialog.accept();
    });

    const deleteButton = await nodeElement.$('.AknFieldContainer .AknIconButton--trash');
    await deleteButton.click();
  };

  const cancelDeletion = async () => {
    await page.evaluate(attributes => {
      const button = attributes.querySelector('.AknFieldContainer .AknIconButton--trash');

      button.style.width = '20px';
      button.style.height = '20px';
    }, nodeElement);

    page.on('dialog', async dialog => {
      await dialog.dismiss();
    });

    const deleteButton = await nodeElement.$('.AknFieldContainer .AknIconButton--trash');
    await deleteButton.click();
  };

  return {hasAttribute, isLoaded, isEmpty, remove, cancelDeletion};
};

module.exports = Attributes;
