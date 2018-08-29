const Attributes = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent .AknSubsection');

    return true;
  };

  const hasAttribute = async (code, type) => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknSubsection .AknFieldContainer[data-placeholder="false"]');
    const attribute = await nodeElement.$(
      `.AknFieldContainer[data-identifier="${code}"][data-type="${type}"][data-placeholder="false"]`
    );

    return attribute !== null;
  };

  const isEmpty = async () => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknSubsection .AknGridContainer-noData');

    return true;
  };

  const remove = async attributeIdentifier => {
    await page.evaluate(
      (attributes, attributeIdentifier) => {
        const button = attributes.querySelector(
          `.AknFieldContainer[data-identifier="${attributeIdentifier}"][data-placeholder="false"] .AknIconButton--trash`
        );

        button.style.width = '20px';
        button.style.height = '20px';
      },
      nodeElement,
      attributeIdentifier
    );

    page.on('dialog', async dialog => {
      await dialog.accept();
    });

    const deleteButton = await nodeElement.$(
      `.AknFieldContainer[data-identifier="${attributeIdentifier}"][data-placeholder="false"] .AknIconButton--trash`
    );
    await deleteButton.click();
  };

  const edit = async attributeIdentifier => {
    await page.waitFor(
      `.AknFieldContainer[data-identifier="${attributeIdentifier}"][data-placeholder="false"] .AknIconButton--edit`
    );
    await page.evaluate(
      (attributes, attributeIdentifier) => {
        const button = attributes.querySelector(
          `.AknFieldContainer[data-identifier="${attributeIdentifier}"][data-placeholder="false"] .AknIconButton--edit`
        );

        button.style.width = '20px';
        button.style.height = '20px';
      },
      nodeElement,
      attributeIdentifier
    );

    page.on('dialog', async dialog => {
      await dialog.accept();
    });

    const deleteButton = await nodeElement.$(
      `.AknFieldContainer[data-identifier="${attributeIdentifier}"][data-placeholder="false"] .AknIconButton--edit`
    );
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

  return {hasAttribute, isLoaded, isEmpty, remove, cancelDeletion, edit};
};

module.exports = Attributes;
