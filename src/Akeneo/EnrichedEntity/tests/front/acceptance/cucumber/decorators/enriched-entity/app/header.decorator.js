const Header = async (nodeElement, createElementDecorator, page) => {
  const clickOnCreateButton = async () => {
    // As the button doesn't have any size, we need to make it clickable by giving him a size
    await page.evaluate(sidebar => {
      const button = sidebar.querySelector('.AknButton');

      button.style.width = '100px';
      button.style.height = '100px';
    }, nodeElement);

    const button = await nodeElement.$('.AknButton');
    await button.click();
  };

  return {clickOnCreateButton};
};

module.exports = Header;
