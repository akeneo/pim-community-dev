import 'expect-puppeteer';
const fs = require('fs');

import {toMatchImageSnapshot} from 'jest-image-snapshot';

expect.extend({toMatchImageSnapshot});

console.log(Object.values(JSON.parse(fs.readFileSync('./stories.json').toString('utf8')).stories));
const stories = Object.values(JSON.parse(fs.readFileSync('./stories.json').toString('utf8')).stories) as {id: string}[];

describe('Checkbox visual tests', () => {
  stories.map((story) => {
    if (story.id.indexOf('components') !== 0) {
      return;
    }

    it(`Renders ${story.id} correctly`, async () => {
      await page.goto(`http://localhost:6006/iframe.html?id=${story.id}`);
      const root = await page.$('#root');
      if (null === root) return;

      const image = await root.screenshot();

      expect(image).toMatchImageSnapshot();
    });
  });
});
