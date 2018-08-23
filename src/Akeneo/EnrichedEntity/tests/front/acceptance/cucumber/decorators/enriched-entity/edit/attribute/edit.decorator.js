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
    const required = await nodeElement.$('.AknFieldContainer[data-code="required"] input');
    const requiredLabel = await nodeElement.$('.AknFieldContainer[data-code="required"] label');
    const currentValue = await required.getProperty('checked');

    if (value != currentValue._remoteObject.value) {
      await requiredLabel.click();
    }
  };

  const setIsTextarea = async value => {
    const isTextarea = await nodeElement.$('.AknFieldContainer[data-code="isTextarea"] input');
    const isTextareaLabel = await nodeElement.$('.AknFieldContainer[data-code="isTextarea"] label');
    const currentValue = await isTextarea.getProperty('checked');

    if (value != currentValue._remoteObject.value) {
      await isTextareaLabel.click();
    }
  };

  const setIsRichTextEditor = async value => {
    const isRichTextEditor = await nodeElement.$('.AknFieldContainer[data-code="isRichTextEditor"] input');
    const isRichTextEditorLabel = await nodeElement.$('.AknFieldContainer[data-code="isRichTextEditor"] label');
    const currentValue = await isRichTextEditor.getProperty('checked');

    if (value != currentValue._remoteObject.value) {
      await isRichTextEditorLabel.click();
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
  };
};

module.exports = AttributeEdit;
