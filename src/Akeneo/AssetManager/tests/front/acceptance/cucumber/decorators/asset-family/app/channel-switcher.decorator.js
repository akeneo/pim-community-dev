const ChannelSwitcher = async (nodeElement, createElementDecorator, page) => {
  const switchChannel = async channel => {
    await page.waitForSelector('.channel-switcher.AknDropdown');
    const openButton = await nodeElement.$('.AknActionButton[data-identifier]');
    await openButton.click();
    await page.waitForSelector('.channel-switcher.AknDropdown .AknDropdown-menuLink');
    const channelButton = await nodeElement.$(`.AknDropdown-menuLink[data-identifier="${channel}"]`);
    await channelButton.click();

    return await page.waitForSelector(`.AknActionButton-highlight[data-identifier="${channel}"]`);
  };

  return {switchChannel};
};

module.exports = ChannelSwitcher;
