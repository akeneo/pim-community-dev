const ManageOptionModal = async (nodeElement, createElementDecorator, page) => {
  const $ = null;

  const isLockedOptionCode = async code => {
    await page.waitForSelector(`.AknOptionEditor-translator tr[data-code="${code}"]`, {timeout: 2000});
    await nodeElement.$(`tr[data-code="${code}"] input[name="code"].AknTextField--disabled`);

    return true;
  };

  const isLockedOptionLabel = async code => {
    await page.waitForSelector(`.AknOptionEditor-translator tr[data-code="${code}"]`, {timeout: 2000});
    const codeInput = await nodeElement.$(`tr[data-code="${code}"] input[name="label"]`);

    return null !== codeInput.$('.AknTextField--disabled');
  };

  const hasNewOption = async () => {
    try {
      await page.waitForSelector('tr[data-code=""] input[name="code"]', {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  };

  const newOptionCode = async code => {
    const newCodeInput = await nodeElement.$('tr[data-code=""] input[name="code"]');

    newCodeInput.type(code);

    await page.waitForSelector(`.AknOptionEditor-translator tr[data-code="${code}"]`, {timeout: 9000});
  };

  const newOptionLabel = async label => {
    await page.waitForSelector('tr[data-code=""] input[name="label"]:not(.AknTextField--disabled)');
    const newLabelInput = await nodeElement.$('tr[data-code=""] input[name="label"]');
    await newLabelInput.type(label);
  };

  const newOptionLabelFieldIsDisabled = async () => {
    try {
      await page.waitForSelector('tr[data-code=""] input[name="label"].AknTextField--disabled', {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  }

  const getOptionCodeValue = async code => {
    const codeInput = await nodeElement.$(`tr[data-code="${code}"] input[name="code"]`);
    const codeInputProperty = await codeInput.getProperty('value');

    return await codeInputProperty.jsonValue();
  };

  const hasOption = async () => {
    try {
      await page.waitForSelector('tr[data-code="${code}"]', {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  };

  const removeOption = async code => {
    const removeOptionButton = await nodeElement.$(`tr[data-code="${code}"] .AknOptionEditor-remove`);
    await removeOptionButton.click();
  };

  const hasRemoveOptionButton = async code => {
    try {
      await page.waitForSelector(`tr[data-code="${code}"] .AknOptionEditor-remove`, {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  };

  const save = async () => {
    await page.evaluate(edit => {
      const button = edit.querySelector('.AknButton.AknButton--apply');
      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const saveButton = await nodeElement.$('.AknButton.AknButton--apply');
    await saveButton.click();
  };

  const cancel = async () => {
    await page.evaluate(edit => {
      const button = edit.querySelector('.AknFullPage-cancel');
      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const cancelButton = await nodeElement.$('.AknFullPage-cancel');
    await cancelButton.click();
  };

  const codeHasLabel = async (code, expectedLabel) => {
    const option = await nodeElement.$(`tr[data-code="${code}"] input[name="label"]`);
    if (null === option) {
      throw new Error(`Row for code ${code} not found`);
    }
    const label = await option.getProperty('value');
    const actualLabel = await label.jsonValue();

    return expectedLabel === actualLabel;
  };

  const helperContains = async expectedLabel => {
    await page.waitForSelector('.AknOptionEditor-helper input', {timeout: 2000});
    const translations = await nodeElement.$$('.AknOptionEditor-helper input');

    for (const label of translations) {
      const elem = await label.getProperty('value');
      const actualLabel = await elem.jsonValue();
      if (actualLabel === expectedLabel) {
        return true;
      }
    }

    return false;
  };

  const labelHasFocus = async code => {
    await page.waitForSelector(`.AknOptionEditor-translator tr[data-code="${code}"]`, {timeout: 2000});

    return await page.evaluate(code => {
      const isLabelInputFocused = 'label' === document.activeElement.name;
      const isRightRowFocused =
        code ===
        $(document.activeElement)
          .closest('[data-code]')
          .data('code');

      return isLabelInputFocused && isRightRowFocused;
    }, code);
  };

  const codeHasFocus = async code => {
    await page.waitForSelector(`.AknOptionEditor-translator tr[data-code="${code}"]`, {timeout: 2000});

    return await page.evaluate(code => {
      const isCodeInputFocused = 'code' === document.activeElement.name;
      const isRightRowFocused =
        code ===
        $(document.activeElement)
          .closest('[data-code]')
          .data('code');

      return isCodeInputFocused && isRightRowFocused;
    }, code);
  };

  const focusCode = async code => {
    await page.waitForSelector(`.AknOptionEditor-translator tr[data-code="${code}"]`, {timeout: 2000});
    const option = await nodeElement.$(`tr[data-code="${code}"] input[name="code"]`);
    option.focus();
  };

  const hasError = async code => {
    await page.waitForSelector(
      undefined === code
        ? '.AknOptionEditor-translator tr .AknFieldContainer-validationErrors'
        : `.AknOptionEditor-translator tr[data-code="${code}"] .AknFieldContainer-validationErrors`,
      {timeout: 1000}
    );

    return true;
  };

  const hasGeneralError = async () => {
    await page.waitForSelector('tr:last-child .AknFieldContainer-validationErrors', {timeout: 2000});

    return true;
  };

  return {
    isLockedOptionCode,
    isLockedOptionLabel,
    hasNewOption,
    newOptionCode,
    newOptionLabel,
    newOptionLabelFieldIsDisabled,
    getOptionCodeValue,
    removeOption,
    hasOption,
    hasRemoveOptionButton,
    save,
    cancel,
    codeHasLabel,
    helperContains,
    labelHasFocus,
    codeHasFocus,
    focusCode,
    hasError,
    hasGeneralError,
  };
};

module.exports = ManageOptionModal;
