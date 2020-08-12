import 'expect-puppeteer';

import {toMatchImageSnapshot} from 'jest-image-snapshot';

expect.extend({toMatchImageSnapshot});

describe('Dummy visual tests', () => {
  it('Renders standard dummy component correctly', async () => {
    await page.goto('http://localhost:6006/iframe.html?id=components-dummy--standard');
    const root = await page.$('#root');
    if (null === root) return;

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot();
  });

  it('Renders standard dummy component varying by size correctly', async () => {
    await page.goto('http://localhost:6006/iframe.html?id=components-dummy--size');
    const root = await page.$('#root');
    if (null === root) return;

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot();
  });

  it('Renders standard dummy component varying by type correctly', async () => {
    await page.goto('http://localhost:6006/iframe.html?id=components-dummy--type');
    const root = await page.$('#root');
    if (null === root) return;

    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot();
  });
});
