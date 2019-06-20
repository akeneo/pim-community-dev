const LocaleSwitcher = async (nodeElement, createElementDecorator, page) => {
  const switchLocale = async locale => {
    await page.waitForSelector('.locale-switcher.AknDropdown');
    const openButton = await nodeElement.$('.AknActionButton[data-identifier]');
    await openButton.click();
    await page.waitForSelector('.locale-switcher.AknDropdown .AknDropdown-menuLink');
    const localeButton = await nodeElement.$(`.AknDropdown-menuLink[data-identifier="${locale}"]`);
    await localeButton.click();

    return await page.waitForSelector(`.AknActionButton-highlight[data-identifier="${locale}"]`);
  };

  return {switchLocale};
};

module.exports = LocaleSwitcher;
