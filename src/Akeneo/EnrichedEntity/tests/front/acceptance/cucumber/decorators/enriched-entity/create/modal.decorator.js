const Modal = async (nodeElement, createElementDecorator, page) => {
  const setCode = async value => {
    const code = await nodeElement.$('.AknTextField[name="code"]');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknTextField[name="code"]').value = '');
    }, nodeElement);

    await code.type(value);
  };

  const setLabel = async value => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknTextField[name="label"]').value = '');
    }, nodeElement);

    await label.type(value);
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

  return {setCode, setLabel, save, getValidationMessageForCode};
};

module.exports = Modal;
