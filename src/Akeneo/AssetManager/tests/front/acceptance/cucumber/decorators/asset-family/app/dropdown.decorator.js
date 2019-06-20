const Dropdown = async (nodeElement, createElementDecorator, page) => {
  const select = async value => {
    await page.waitForSelector('.AknDropdown');
    const openButton = await nodeElement.$('.AknActionButton[data-identifier]');
    await openButton.click();
    await page.waitForSelector('.AknDropdown .AknDropdown-menuLink');
    const valueButton = await nodeElement.$(`.AknDropdown-menuLink[data-identifier="${value}"]`);
    await valueButton.click();

    return await page.waitForSelector(`.AknActionButton-highlight[data-identifier="${value}"]`);
  };

  return {select};
};

module.exports = Dropdown;
