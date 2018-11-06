const path = require('path');
const {getRequestContract, listenRequest} = require('../../tools');
const AttributeEdit = require('../../decorators/reference-entity/edit/attribute/edit.decorator');
const ManageOptionModal = require('../../decorators/reference-entity/edit/attribute/manage-option-modal.decorator');
const LocaleSwitcher = require('../../decorators/reference-entity/app/locale-switcher.decorator');

const {
  decorators: {createElementDecorator},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Given, When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    AttributeEdit: {
      selector: '.AknDefault-mainContent .AknQuickEdit',
      decorator: AttributeEdit,
    },
    ManageOptionModal: {
      selector: '.manageOptionModal',
      decorator: ManageOptionModal,
    },
    ManageOptionLocaleSelector: {
      selector: '.manageOptionModal .locale-switcher',
      decorator: LocaleSwitcher,
    },
  };

  const getElement = createElementDecorator(config);

  Given('the reference entity designer', async function() {
    const requestContract = getRequestContract('Attribute/ListDetails/Ok/designer.json');
    await listenRequest(this.page, requestContract);
  });

  Given('the user manages the options of the attribute', async function() {
    const edit = await await getElement(this.page, 'AttributeEdit');
    edit.showManageOptionModal();
  });

  Then('the code of the {string} option should be locked', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.isLockedOptionCode(code);
  });

  When('the user adds the new option label {string}', async function(label) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.newOptionLabel(label);
  });

  When('the user adds the new option code {string}', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.newOptionCode(code);
  });

  When('the user removes the option {string}', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    manageOption.removeOption(code);
  });

  Then('the user saves successfully', async function() {
    const requestContract = getRequestContract('Attribute/Edit/Option/ok.json');
    await listenRequest(this.page, requestContract);
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.save();
  });

  Then('the code of the option {string} should be {string}', async function(label, code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    const newCode = await manageOption.getOptionCodeValue(code);

    assert.strictEqual(newCode, code);
  });

  Then('the user should see a success message on the edit attribute page', async function() {
    const edit = await await getElement(this.page, 'AttributeEdit');
    const hasSuccessNotification = await edit.hasSuccessNotification();
    assert.strictEqual(hasSuccessNotification, true);
  });

  Then('the option {string} should not be in the list', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    const hasOption = await manageOption.hasOption(code);

    assert.strictEqual(hasOption, false);
  });

  Then('the translation helper should display {string}', async function(expectedLabel) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    const found = await manageOption.helperContains(expectedLabel);

    assert.strictEqual(
      true,
      found,
      `Expected label "${expectedLabel}" to be displayed in the helper, but it was not found.`
    );
  });

  When('the user goes to the next option to translate with the keyboard', async function() {
    await this.page.waitForSelector('.AknOptionEditor-translator', {timeout: 2000});
    await this.page.keyboard.press('Enter');
  });

  When('the user focuses the {string} option code', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.focusCode(code);
  });

  Then('the focus should be on the {string} option label', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    const hasFocus = await manageOption.labelHasFocus(code);
    if (!hasFocus) {
      throw new Error(`Expected label of option "${code}" to be focused`);
    }
  });

  Then('the focus should be on the {string} option code', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    const hasFocus = await manageOption.codeHasFocus(code);
    assert.strictEqual(hasFocus, true, `Expected label of option "${code}" to be focused`);
  });

  When('the user cancels the changes by confirming the warning message', async function() {
    let dialogExist = false;
    this.page.on('dialog', async dialog => {
      dialogExist = true;
      await dialog.accept();
    });

    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.cancel();
    assert.strictEqual(dialogExist, true, 'Expected dialog to be shown but it did not');
  });

  When('the user changes the locale to translate to {string}', async function(locale) {
    const manageOption = await await getElement(this.page, 'ManageOptionLocaleSelector');
    await manageOption.switchLocale(locale);
  });

  Then('the label of the option {string} should be {string}', async function(optionCode, optionLabel) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    assert.strictEqual(
      await manageOption.codeHasLabel(optionCode, optionLabel),
      true,
      `Expected option of code "${optionCode}", to have the label "${optionLabel}", but was not found.`
    );
  });

  Then('the translation helper displays {string}', async function(otherLabel) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    assert.strictEqual(
      await manageOption.helperContains(otherLabel),
      true,
      `Expected label "${otherLabel}" to be displayed in the helper, but it was not found.`
    );
  });

  Then('the user cannot save the options successfully because the option is not valid', async function() {
    const requestContract = getRequestContract('Attribute/Edit/Option/invalid_option_code_regular_expression.json');
    await listenRequest(this.page, requestContract);
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.save();
  });

  Then('the user cannot save the options successfully because an option is duplicated', async function() {
    const requestContract = getRequestContract('Attribute/Edit/Option/invalid_options_duplicated.json');
    await listenRequest(this.page, requestContract);
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.save();
  });

  Then('the user cannot save the options successfully because the limit of options is reached', async function() {
    const requestContract = getRequestContract('Attribute/Edit/Option/limit_of_options_reached.json');
    await listenRequest(this.page, requestContract);
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    await manageOption.save();
  });

  Then('there is an error message next to the {string} field', async function(code) {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    assert.strictEqual(await manageOption.hasError(code), true, `No validation error found for code: "${code}"`);
  });

  Then('there is an error message next to the translator', async function() {
    const manageOption = await await getElement(this.page, 'ManageOptionModal');
    assert.strictEqual(await manageOption.hasGeneralError(), true, 'No general validation error found');
  });
};
