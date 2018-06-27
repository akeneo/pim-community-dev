const Menu = async (nodeElement, createElementDecorator, page) => {
  const clickOnItem = async (itemValue) => {
    const menuItems = await nodeElement.$$('.AknHeader-menuItem');
    menuItems.map(async (menuItem) => {
      if (itemValue === menuItem.getProperty('value').jsonValue()) {
        await menuItem.click();
      }
    });
  };

  return {clickOnItem};
};

module.exports = Menu;
