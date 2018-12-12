const Modal = async (nodeElement, createElementDecorator, page) => {
  const fillField = async (id, value) => {
    const field = await nodeElement.$(`.AknTextField[id="${id}"]`);
    await page.evaluate(
      (properties, id) => {
        return (properties.querySelector(`.AknTextField[id="${id}"]`).value = '');
      },
      nodeElement,
      id
    );

    await field.type(value);
  };
  const switchField = async (id, value) => {
    if (value) {
      const switchElement = await nodeElement.$(`.AknSwitch-input[id="${id}"]:not(:checked)`);
      switchElement.click();
    } else {
      const switchElement = await nodeElement.$(`.AknSwitch-input[id="${id}"]:not(:checked)`);
      switchElement.click();
    }
  };

  const save = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.evaluate(modal => {
      const button = modal.querySelector('.AknButton.AknButton--apply');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const saveButton = await nodeElement.$('.AknButton--apply');
    await saveButton.click();
  };

  const getValidationMessageForCode = async () => {
    try {
      await page.waitForSelector('.error-message', {timeout: 2000});
    } catch (error) {
      return '';
    }

    const validationError = await nodeElement.$('.error-message');
    const property = await validationError.getProperty('textContent');

    return await property.jsonValue();
  };

  const toggleCreateAnother = async () => {
    const checkbox = await page.$('.AknFieldContainer[data-code="create_another"] .AknCheckbox');
    checkbox.click();
  };

  const select = async (selector, value) => {
    await page.waitForSelector(`${selector} .AknDropdown`);
    const openButton = await page.$(`${selector} .AknButton`);
    await openButton.click();
    await page.waitForSelector(`${selector} .AknDropdown .AknDropdown-menuLink`);
    const valueButton = await page.$(`${selector} .AknDropdown-menuLink[data-identifier="${value}"]`);
    await valueButton.click();

    return await page.waitForSelector(`.AknDropdown-menuLink--active[data-identifier="${value}"]`);
  };

  return {fillField, switchField, save, getValidationMessageForCode, select, toggleCreateAnother};
};

module.exports = Modal;
