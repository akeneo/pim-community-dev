const Enrich = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent');

    return true;
  };

  const getTabCode = async () => {
    return await page.evaluate(properties => {
      return properties.dataset.tab;
    }, nodeElement);
  };

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

  const fillSelectField = async (id, value) => {
    await page.select(`.AknSelectField[id="${id}"]`, value);
  };

  const fillRecordSelectField = async (id, value) => {
    const field = await nodeElement.$(`.record-selector[id="${id}"]`);
    await page.evaluate(
      (properties, id, value) => {
        return (properties.querySelector(`.record-selector[id="${id}"]`).value = '');
      },
      nodeElement,
      id,
      value
    );

    await field.type(value);
  };

  const fillUploadField = async (id, value) => {
    const field = await nodeElement.$(`.AknImage-updater[id="${id}"]`);
    await page.evaluate(
      (properties, id) => {
        return (properties.querySelector(`.AknImage-updater[id="${id}"]`).value = '');
      },
      nodeElement,
      id
    );

    await field.uploadFile(value);
  };

  const getLabel = async () => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    const labelProperty = await label.getProperty('value');

    return await labelProperty.jsonValue();
  };

  const setLabel = async value => {
    const label = await nodeElement.$('.AknTextField[name="label"]');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknTextField[name="label"]').value = '');
    }, nodeElement);

    await label.type(value);
  };

  const clickOnDeleteButton = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.evaluate(edit => {
      const button = edit.querySelectorAll('.AknDropdown-menuLink');

      button[0].style.width = '100px';
      button[0].style.height = '100px';
    }, nodeElement);

    const deleteButton = await nodeElement.$('.AknDropdown-menuLink:first-child');
    await deleteButton.click();
  };

  const hasDeleteButton = async () => {
    try {
      await page.waitForSelector('.AknDropdown-menuLink:first-child', {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  };

  const isFilled = async field => {
    try {
      await page.waitForSelector(`div[data-code="${field}"] .AknBadge--small:not(.AknBadge--hidden)`, {timeout: 2000});
    } catch (error) {
      return true;
    }

    return false;
  };

  return {
    isLoaded,
    getLabel,
    setLabel,
    getTabCode,
    fillField,
    fillUploadField,
    fillSelectField,
    fillRecordSelectField,
    clickOnDeleteButton,
    hasDeleteButton,
    isFilled,
    clickOnDeleteButton,
    hasDeleteButton,
  };
};

module.exports = Enrich;
