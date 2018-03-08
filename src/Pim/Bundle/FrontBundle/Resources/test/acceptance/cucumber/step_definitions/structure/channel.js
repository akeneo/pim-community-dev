const {Given, Then} = require('cucumber');
const assert = require('assert');
const {createChannel} = require('../fixtures');
const {answerJson, csvToArray} = require('../tools');

Given('the following channels with locales:', async function(rawChannels) {
  const channels = rawChannels.hashes().map(({code, locales}) => createChannel(code, csvToArray(locales)));
  this.page.on('request', request => {
    if (request.url().includes('/configuration/channel/rest')) {
      answerJson(request, channels);
    }
  });
});

Given('the channels {string}', async function(csvChannelCodes) {
  const channels = csvToArray(csvChannelCodes).map(channelCode => createChannel(channelCode));

  this.page.on('request', request => {
    if (request.url().includes('/configuration/channel/rest')) {
      answerJson(request, channels);
    }
  });
});

Then('the channel should be {string}', async function(expectedChannel) {
  const actualChannel = await this.page.evaluate(element => {
    return element.dataset.identifier;
  }, await this.page.waitFor('.channel-switcher .AknColumn-value'));

  assert.equal(actualChannel, expectedChannel);
});

Then('I switch the channel to {string}', async function(channel) {
  await this.page.waitFor('.channel-switcher .AknActionButton');
  await this.page.click('.channel-switcher .AknActionButton');
  await this.page.waitFor(`.channel-switcher .AknDropdown-menuLink[data-identifier="${channel}"]`);
  await this.page.click(`.channel-switcher .AknDropdown-menuLink[data-identifier="${channel}"]`);
});
