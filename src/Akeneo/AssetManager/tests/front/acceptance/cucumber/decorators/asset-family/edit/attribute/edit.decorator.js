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

  const disabledLabel = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="label"] .AknTextField--disabled');
    } catch (error) {
      return false;
    }

    return true;
  };

  const setIsRequired = async value => {
    const required = await nodeElement.$('.AknFieldContainer[data-code="isRequired"] .AknCheckbox');
    const currentValue = await required.getProperty('data-checked');

    if (value != currentValue._remoteObject.value) {
      await required.click();
    }
  };

  const disabledIsRequired = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="isRequired"] .AknCheckbox--disabled');
    } catch (error) {
      return false;
    }

    return true;
  };

  const setIsTextarea = async value => {
    const required = await nodeElement.$('.AknFieldContainer[data-code="isTextarea"] .AknCheckbox');
    const currentValue = await required.getProperty('data-checked');

    if (value != currentValue._remoteObject.value) {
      await required.click();
    }
  };

  const disabledIsTextarea = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="isTextarea"] .AknCheckbox--disabled');
    } catch (error) {
      return false;
    }

    return true;
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

  const disabledValidationRule = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="validationRule"] .AknDropdown-menu', {hidden: true});
    } catch (error) {
      return false;
    }

    return true;
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

  const disabledMaxLength = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="maxLength"] .AknTextField--disabled');
    } catch (error) {
      return false;
    }

    return true;
  };

  const setMaxFileSize = async value => {
    const maxFileSize = await nodeElement.$('.AknFieldContainer[data-code="maxFileSize"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="maxFileSize"] .AknTextField').value = '');
    }, nodeElement);

    await maxFileSize.type(value);
  };

  const disabledMaxFileSize = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="maxFileSize"] .AknTextField--disabled');
    } catch (error) {
      return false;
    }

    return true;
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

  const disabledAllowedExtensions = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="allowedExtensions"] select:disabled');
    } catch (error) {
      return false;
    }

    return true;
  };

  const setDecimalsAllowed = async value => {
    const decimalsAllowed = await nodeElement.$('.AknFieldContainer[data-code="decimalsAllowed"] .AknCheckbox');
    const currentValue = await decimalsAllowed.getProperty('data-checked');

    if (value != currentValue._remoteObject.value) {
      await decimalsAllowed.click();
    }
  };

  const setMinValue = async value => {
    const minValue = await nodeElement.$('.AknFieldContainer[data-code="minValue"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="minValue"] .AknTextField').value = '');
    }, nodeElement);

    await minValue.type(value);
  };

  const setMaxValue = async value => {
    const maxValue = await nodeElement.$('.AknFieldContainer[data-code="maxValue"] .AknTextField');
    await page.evaluate(properties => {
      return (properties.querySelector('.AknFieldContainer[data-code="maxValue"] .AknTextField').value = '');
    }, nodeElement);

    await maxValue.type(value);
  };

  const disabledDecimalsAllowed = async () => {
    try {
      await page.waitForSelector('.AknFieldContainer[data-code="decimalsAllowed"] .AknCheckbox--disabled');
    } catch (error) {
      return false;
    }

    return true;
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
      await page.waitForSelector('[role="success"]', {timeout: 2000});
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
    disabledLabel,
    setIsRequired,
    disabledIsRequired,
    setIsTextarea,
    disabledIsTextarea,
    setIsRichTextEditor,
    setValidationRule,
    disabledValidationRule,
    setRegularExpression,
    setMaxLength,
    disabledMaxLength,
    setMaxFileSize,
    disabledMaxFileSize,
    setAllowedExtensions,
    disabledAllowedExtensions,
    isLoaded,
    showManageOptionModal,
    hasSuccessNotification,
    setDecimalsAllowed,
    setMinValue,
    setMaxValue,
    disabledDecimalsAllowed,
  };
};

module.exports = AttributeEdit;
