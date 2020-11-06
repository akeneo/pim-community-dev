import 'expect-puppeteer';
import {toMatchImageSnapshot} from 'jest-image-snapshot';
expect.extend({toMatchImageSnapshot});

it('Renders Confirm Modal overlay correctly', async () => {
  await page.goto('http://localhost:6006/iframe.html?id=patterns-overlays--confirm-modal');
  const root = await page.waitFor('#root');
  if (null === root) throw new Error('Cannot find root element');

  const openButton = await page.$('button');
  if (null === openButton) throw new Error('Cannot find button');
  await openButton.click();

  await new Promise(resolve => setTimeout(resolve, 500));

  const modalRoot = await page.$('#modal-root > :first-child');
  if (null === modalRoot) throw new Error('Cannot find modal root');
  const image = await modalRoot.screenshot();

  expect(image).toMatchImageSnapshot({
    failureThreshold: 1,
    failureThresholdType: 'percent',
  });
});
