import 'expect-puppeteer';
import {toMatchImageSnapshot} from 'jest-image-snapshot';
expect.extend({toMatchImageSnapshot});

const storyIds = ['standard', 'with-illustration'];
test.each(storyIds)('Test modal %s is displayed correctly', async storyId => {
  console.log('1');

  await page.goto(`http://localhost:6006/iframe.html?id=components-modal--${storyId}`);
  console.log('2');

  const root = await page.waitFor('#root');
  console.log('3');

  if (null === root) throw new Error('Cannot find root element');
  console.log('4');

  const openButton = await page.$('button');
  console.log('5');
  if (null === openButton) throw new Error('Cannot find button');
  console.log('6');
  console.log(page.viewport());
  console.log(openButton);

  await browser.waitAndClick(openButton);
  console.log('7');

  await new Promise(resolve => setTimeout(resolve, 500));
  console.log('8');

  const modalRoot = await page.$('#modal-root > :first-child');
  console.log('9');

  if (null === modalRoot) throw new Error('Cannot find modal root');
  console.log('10');

  const image = await modalRoot.screenshot();
  console.log('11');

  expect(image).toMatchImageSnapshot({
    failureThreshold: 1,
    failureThresholdType: 'percent',
  });
});
