const Modal = async (nodeElement, createElementDecorator, page) => {
  const fillField = async (id, value) => {
    const field = await nodeElement.$(`.AknTextField[id="${id}"]`);
    await page.evaluate((properties, id) => {
      return (properties.querySelector(`.AknTextField[id="${id}"]`).value = '');
    }, nodeElement, id);

    await field.type(value);
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
    await page.waitForSelector('.error-message');
    const error = await nodeElement.$('.error-message');
    const property = await error.getProperty('textContent');

    return await property.jsonValue();
  };

  return {fillField, save, getValidationMessageForCode};
};

module.exports = Modal;
