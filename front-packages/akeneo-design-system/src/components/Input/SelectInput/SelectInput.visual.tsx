import 'expect-puppeteer';
import {toMatchImageSnapshot} from 'jest-image-snapshot';
expect.extend({toMatchImageSnapshot});

const storyIds = ['standard', 'read-only', 'invalid', 'vertical-position', 'large'];
test.each(storyIds)('Test select input %s is displayed correctly', async storyId => {
  await page.goto(`http://localhost:6006/iframe.html?id=components-inputs-select-input--${storyId}`);
  const root = await page.waitFor('#root');
  if (null === root) throw new Error('Cannot find root element');

  const firstInput = await page.$('input');
  if (null === firstInput) throw new Error('Cannot find input');
  await firstInput.click();

  await new Promise(resolve => setTimeout(resolve, 500));

  const select = await page.$('#root > :first-child');
  if (null === select) throw new Error('Cannot find input root');
  const image = await select.screenshot();

  expect(image).toMatchImageSnapshot({
    failureThreshold: 1,
    failureThresholdType: 'percent',
  });
});
