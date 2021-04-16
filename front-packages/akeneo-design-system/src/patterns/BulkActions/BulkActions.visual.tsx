import 'expect-puppeteer';
import {toMatchImageSnapshot} from 'jest-image-snapshot';
expect.extend({toMatchImageSnapshot});

const storyIds = ['big-table', 'small-table', 'with-cards'];

test.each(storyIds)('Test Bulk Actions %s is displayed correctly', async storyId => {
  await page.goto(`http://localhost:6006/iframe.html?id=patterns-bulk-actions--${storyId}`);
  const root = await page.waitFor('#root');
  if (null === root) throw new Error('Cannot find root element');

  const firstCheckbox = await page.$('div[role="checkbox"]');
  if (null === firstCheckbox) throw new Error('Cannot find checkbox');
  await firstCheckbox.click();

  await new Promise(resolve => setTimeout(resolve, 500));

  const image = await root.screenshot();

  expect(image).toMatchImageSnapshot({
    failureThreshold: 1,
    failureThresholdType: 'percent',
  });
});
