const Properties = require('./edit/properties.decorator');
const Sidebar = require('./app/sidebar.decorator');

const {
  decorators: {createElementDecorator},
} = require('../../test-helpers.js');

const config = {
  Sidebar: {
    selector: '.AknColumn',
    decorator: Sidebar,
  },
  Properties: {
    selector: '.AknDefault-mainContent',
    decorator: Properties,
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

  const getProperties = async () => {
    return await await getElement(page, 'Properties');
  };

  const isUpdated = async () => {
    await page.waitForSelector('.updated-status', {visible: true});

    return true;
  };

  const isSaved = async () => {
    // await page.setRequestInterception(true);
    // page.on('request', async request => {
    //   debugger;
    //   if (request.url().includes('/rest/enriched_entity/designer')) {
    //     debugger;
    //     await page.waitForSelector('.updated-status', {hidden: true});
    //   }
    // });
    // await page.waitForNavigation({waitUntil: 'networkidle0'});
    await page.waitForSelector('.updated-status', {hidden: true});
    // debugger;

    return true;
  };

  const save = async () => {
    await page.evaluate(edit => {
      const button = edit.querySelector('.AknButton.save');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const saveButton = await nodeElement.$('.AknButton.save');
    await saveButton.click();
  };

  return {isLoaded, getSidebar, getProperties, isUpdated, isSaved, save};
};

module.exports = Edit;
