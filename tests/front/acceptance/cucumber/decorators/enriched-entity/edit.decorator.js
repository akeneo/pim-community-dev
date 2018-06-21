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
  }
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

  const save = async () => {
    const saveButton = await nodeElement.$('.AknButton.save');

    await saveButton.click();
  };

  return {isLoaded, getSidebar, getProperties, save};
};

module.exports = Edit;
