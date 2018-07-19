const Header = async (nodeElement, createElementDecorator, page) => {
  const clickOnCreateButton = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.waitForSelector('.AknButton');
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

    return null !== await nodeElement.$('.AknButton');
  };

  return {clickOnCreateButton, isCreateButtonVisible};
};

module.exports = Header;
