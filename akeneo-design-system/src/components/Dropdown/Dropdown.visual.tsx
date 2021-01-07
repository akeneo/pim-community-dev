import 'expect-puppeteer';
import {toMatchImageSnapshot} from 'jest-image-snapshot';
expect.extend({toMatchImageSnapshot});

const storyIds = ['standard', 'simple-item', 'item-with-selection', 'item-with-image'];
test.each(storyIds)('Test dropdown %s is displayed correctly', async storyId => {
  await page.goto(`http://localhost:6006/iframe.html?id=components-dropdown--${storyId}`);
  const root = await page.waitFor('#root');
  if (null === root) throw new Error('Cannot find root element');

  const openButton = await page.$('button');
  if (null === openButton) throw new Error('Cannot find button');
  await openButton.click();

  await new Promise(resolve => setTimeout(resolve, 500));

  const image = await page.screenshot();

  expect(image).toMatchImageSnapshot({
    failureThreshold: 1,
    failureThresholdType: 'percent',
  });
});
