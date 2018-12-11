const Enrich = require('./edit/enrich.decorator');
const Sidebar = require('../reference-entity/app/sidebar.decorator');
const LocaleSwitcher = require('../reference-entity/app/locale-switcher.decorator');

const config = {
  Sidebar: {
    selector: '.AknColumn',
    decorator: Sidebar,
  },
  Enrich: {
    selector: '.AknDefault-mainContent',
    decorator: Enrich,
  },
  LocaleSwitcher: {
    selector: '.locale-switcher',
    decorator: LocaleSwitcher,
  },
};

const Edit = async (nodeElement, createElementDecorator, page) => {
  const getElement = createElementDecorator(config);

  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent');

    return true;
  };

  const getSidebar = async () => {
    return await await getElement(page, 'Sidebar');
  };

  const getLocaleSwitcher = async () => {
    return await await getElement(page, 'LocaleSwitcher');
  };

  const getCompletenessValue = async () => {
    return await page.evaluate(edit => {
      return edit.querySelector('.AknBadge--big').innerText;
    }, nodeElement);
  };

  const getEnrich = async () => {
    const sidebar = await await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('enrich');

    return await await getElement(page, 'Enrich');
  };

  const isUpdated = async () => {
    await page.waitForSelector('.updated-status', {visible: true});

    return true;
  };

  const isSaved = async () => {
    await page.waitForSelector('.updated-status', {hidden: true});

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

  const hasSuccessNotification = async () => {
    try {
      await page.waitForSelector('.AknFlash--success', {timeout: 2000});
    } catch (error) {
      return false;
    }

    return true;
  };

  const hasErrorNotification = async () => {
    await page.waitForSelector('.AknFlash--error');

    return true;
  };

  const hasNoNotification = async () => {
    await page.waitForSelector('.AknFlash', {hidden: true});

    return true;
  };

  return {
    isLoaded,
    getSidebar,
    getLocaleSwitcher,
    getCompletenessValue,
    getEnrich,
    isUpdated,
    isSaved,
    save,
    getValidationMessageForCode,
    hasSuccessNotification,
    hasErrorNotification,
    hasNoNotification,
  };
};

module.exports = Edit;
