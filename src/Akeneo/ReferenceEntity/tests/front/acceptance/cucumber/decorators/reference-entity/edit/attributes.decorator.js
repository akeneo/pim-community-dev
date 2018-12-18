const Modal = require('../../delete/modal.decorator');

const config = {
  Modal: {
    selector: '.modal',
    decorator: Modal,
  },
};

const Attributes = async (nodeElement, createElementDecorator, page) => {
  const getElement = createElementDecorator(config);

  const isLoaded = async () => {
    await page.waitFor(
      '.AknDefault-mainContent .AknSubsection .AknFieldContainer[data-identifier="system_record_code"]'
    );

    return true;
  };

  const hasAttribute = async (code, type) => {
    await isLoaded();
    const fieldSelector = `[data-identifier="${code}"][data-type="${type}"][data-placeholder="false"]`;

    await page.waitFor(`.AknDefault-mainContent .AknSubsection .AknFieldContainer${fieldSelector}`);

    return true;
  };

  const isEmpty = async () => {
    await isLoaded();
    await page.waitFor('.AknDefault-mainContent .AknSubsection .AknGridContainer-noData');

    return true;
  };

  const remove = async () => {
    await page.evaluate(attributes => {
      const button = attributes.querySelector('.AknQuickEdit .AknButton--delete');

      button.style.width = '20px';
      button.style.height = '20px';
    }, nodeElement);

    const deleteButton = await nodeElement.$('.AknQuickEdit .AknButton--delete');
    await deleteButton.click();

    const modalPage = await await getElement(page, 'Modal');
    await modalPage.confirmDeletion();
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

    const editButton = await nodeElement.$(
      `.AknFieldContainer[data-identifier="${attributeIdentifier}"][data-placeholder="false"] .AknIconButton--edit`
    );
    await editButton.click();
  };

  const cancelDeletion = async () => {
    await page.evaluate(attributes => {
      const button = attributes.querySelector('.AknQuickEdit .AknButton.AknButton--delete');

      button.style.width = '20px';
      button.style.height = '20px';
    }, nodeElement);

    page.on('dialog', async dialog => {
      await dialog.dismiss();
    });

    const deleteButton = await nodeElement.$('.AknQuickEdit .AknButton.AknButton--delete');
    await deleteButton.click();
  };

  return {hasAttribute, isLoaded, isEmpty, remove, cancelDeletion, edit};
};

module.exports = Attributes;
