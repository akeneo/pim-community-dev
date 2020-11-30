const Permission = require('./edit/permission.decorator');
const Properties = require('./edit/properties.decorator');
const Sidebar = require('./app/sidebar.decorator');
const LocaleSwitcher = require('./app/locale-switcher.decorator');
const ChannelSwitcher = require('./app/channel-switcher.decorator');

const config = {
  Sidebar: {
    selector: '.AknColumn',
    decorator: Sidebar,
  },
  Properties: {
    selector: '.AknDefault-mainContent',
    decorator: Properties,
  },
  Permission: {
    selector: '.AknDefault-mainContent',
    decorator: Permission,
  },
  LocaleSwitcher: {
    selector: '.locale-switcher',
    decorator: LocaleSwitcher,
  },
  ChannelSwitcher: {
    selector: '.channel-switcher',
    decorator: ChannelSwitcher,
  },
};

const Edit = async (nodeElement, createElementDecorator, page) => {
  const getElement = createElementDecorator(config);

  const isLoaded = async () => {
    await page.waitFor('.AknDefault-mainContent');

    return true;
  };

  const getSidebar = async () => {
    return await getElement(page, 'Sidebar');
  };

  const getLocaleSwitcher = async () => {
    return await getElement(page, 'LocaleSwitcher');
  };

  const getChannelSwitcher = async () => {
    return await getElement(page, 'ChannelSwitcher');
  };

  const getProperties = async () => {
    const sidebar = await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('property');

    return await getElement(page, 'Properties');
  };

  const getPermission = async () => {
    const sidebar = await getElement(page, 'Sidebar');
    await sidebar.clickOnTab('permission');

    return await getElement(page, 'Permission');
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

  const hasSuccessNotification = async () => {
    await page.waitForSelector('[role="status"]');

    return true;
  };

  const hasErrorNotification = async () => {
    await page.waitForSelector('[role="alert"]');

    return true;
  };

  const hasNoNotification = async () => {
    await page.waitForSelector('[role="alert"], [role="status"]', {hidden: true});

    return true;
  };

  const hasNoSaveButton = async () => {
    await page.waitForSelector('.AknButton--apply', {hidden: true});

    return true;
  };

  return {
    isLoaded,
    getSidebar,
    getLocaleSwitcher,
    getChannelSwitcher,
    getProperties,
    getPermission,
    isUpdated,
    isSaved,
    save,
    hasSuccessNotification,
    hasErrorNotification,
    hasNoNotification,
    hasNoSaveButton,
  };
};

module.exports = Edit;
