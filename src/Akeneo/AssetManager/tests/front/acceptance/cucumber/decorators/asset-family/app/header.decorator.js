const Header = async (nodeElement, createElementDecorator, page) => {
  const clickOnCreateButton = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.waitForSelector('.AknTitleContainer-mainContainer .AknButton');
    await page.evaluate(header => {
      const button = header.querySelector('.AknButton');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const button = await nodeElement.$('.AknButton');
    await button.click();
  };

  const isCreateButtonVisible = async () => {
    await page.waitForSelector('.AknTitleContainer-userIcon');

    return null !== (await nodeElement.$('.AknButton'));
  };

  const clickOnDeleteButton = async () => {
    await page.waitForSelector('.AknSecondaryActions .AknDropdown-menuLink');

    const button = await nodeElement.$('.AknSecondaryActions .AknDropdown-menuLink');
    await button.click();
  };

  const isDeleteButtonVisible = async () => {
    await page.waitForSelector('.AknTitleContainer-rightButton');

    return null !== (await nodeElement.$('.AknSecondaryActions .AknDropdown-menuLink'));
  };

  const hasNoCreateButton = async () => {
    await page.waitForSelector('.AknTitleContainer-mainContainer .AknButton', {hidden: true});

    return true;
  };

  return {clickOnCreateButton, isCreateButtonVisible, isDeleteButtonVisible, clickOnDeleteButton, hasNoCreateButton};
};

module.exports = Header;
