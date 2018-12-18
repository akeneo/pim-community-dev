const AttributeEdit = async (nodeElement, createElementDecorator, page) => {
  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent .QuickEdit');

    return true;
  };

  const setLabel = async value => {
    const label = await nodeElement.$('.AknFieldContainer[data-code="label"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="label"] .AknTextField').value = '');
    }, nodeElement);

    await label.type(value);
  };

  const setIsRequired = async value => {
    const required = await nodeElement.$('.AknFieldContainer[data-code="isRequired"] .AknCheckbox');
    const currentValue = await required.getProperty('data-checked');

    if (value != currentValue._remoteObject.value) {
      await required.click();
    }
  };

  const setIsTextarea = async value => {
    const required = await nodeElement.$('.AknFieldContainer[data-code="isTextarea"] .AknCheckbox');
    const currentValue = await required.getProperty('data-checked');

    if (value != currentValue._remoteObject.value) {
      await required.click();
    }
  };

  const setIsRichTextEditor = async value => {
    const required = await nodeElement.$('.AknFieldContainer[data-code="isRichTextEditor"] .AknCheckbox');
    const currentValue = await required.getProperty('data-checked');

    if (value != currentValue._remoteObject.value) {
      await required.click();
    }
  };

  const setValidationRule = async value => {
    const validationRule = await nodeElement.$(
      `.AknFieldContainer[data-code="validationRule"] .AknDropdown-menuLink[data-identifier="${value}"]`
    );

    await validationRule.click();
  };

  const setRegularExpression = async value => {
    const regularExpression = await nodeElement.$('.AknFieldContainer[data-code="regularExpression"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="regularExpression"] .AknTextField').value = '');
    }, nodeElement);

    await regularExpression.type(value);
  };

  const setMaxLength = async value => {
    const maxLength = await nodeElement.$('.AknFieldContainer[data-code="maxLength"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="maxLength"] .AknTextField').value = '');
    }, nodeElement);

    await maxLength.type(value);
  };

  const setMaxFileSize = async value => {
    const maxFileSize = await nodeElement.$('.AknFieldContainer[data-code="maxFileSize"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="maxFileSize"] .AknTextField').value = '');
    }, nodeElement);

    await maxFileSize.type(value);
  };

  const setAllowedExtensions = async value => {
    const allowedExtensions = await nodeElement.$('.AknFieldContainer[data-code="allowedExtensions"] select');
    await page.evaluate(
      (properties, newValues) => {
        Array.prototype.slice
          .call(properties.querySelectorAll('.AknFieldContainer[data-code="allowedExtensions"] option'))
          .forEach(option => (option.selected = newValues.includes(option.value)));
      },
      nodeElement,
      value.split(',')
    );

    await allowedExtensions.type(value);
  };

  const showManageOptionModal = async () => {
    page.waitForSelector('.AknButton[data-code="manageOption"]');
    await page.evaluate(edit => {
      const button = edit.querySelector('.AknButton[data-code="manageOption"]');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const button = await nodeElement.$('.AknButton[data-code="manageOption"]');
    await button.click();
    await page.waitForSelector('.modal');
  };

  const hasSuccessNotification = async () => {
    try {
      await page.waitForSelector('.AknFlash--success', {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  };

  const isVisible = async property => {
    return null !== (await nodeElement.$(`.AknFieldContainer[data-code="${property}"]`));
  };

  return {
    isVisible,
    setLabel,
    setIsRequired,
    setIsTextarea,
    setIsRichTextEditor,
    setValidationRule,
    setRegularExpression,
    setMaxLength,
    setMaxFileSize,
    setAllowedExtensions,
    isLoaded,
    showManageOptionModal,
    hasSuccessNotification,
  };
};

module.exports = AttributeEdit;
