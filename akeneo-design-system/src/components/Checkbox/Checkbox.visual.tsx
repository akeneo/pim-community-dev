import 'expect-puppeteer';

import {toMatchImageSnapshot} from 'jest-image-snapshot';

expect.extend({toMatchImageSnapshot});

describe('Checkbox visual tests', () => {
  it('Renders standard checkbox component correctly', async () => {
    await page.goto('http://localhost:6006/iframe.html?id=components-checkbox--standard');
    const root = await page.$('#root');
    if (null === root) return;

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot();
  });

  it('Renders checkbox component varying by state', async () => {
    await page.goto('http://localhost:6006/iframe.html?id=components-checkbox--state');
    const root = await page.$('#root');
    if (null === root) return;

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot();
  });

  it('Renders checkbox component varying on disabled', async () => {
    await page.goto('http://localhost:6006/iframe.html?id=components-checkbox--disabled');
    const root = await page.$('#root');
    if (null === root) return;

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot();
  });
});
