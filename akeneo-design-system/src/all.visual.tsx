import 'expect-puppeteer';
import fs from 'fs';

import {toMatchImageSnapshot} from 'jest-image-snapshot';

expect.extend({toMatchImageSnapshot});

type StoriesDump = {
  stories: {
    [storyKey: string]: {
      id: string;
      kind: string;
      name: string;
    };
  };
};

const storyFileContent = fs.readFileSync('./stories.json').toString('utf8');
const storiesDump = JSON.parse(storyFileContent) as StoriesDump;

const stories = Object.values(storiesDump.stories);

describe('Visual tests', () => {
  stories.map(story => {
    if (story.id.indexOf('components') !== 0) {
      return;
    }

    it(`Renders ${story.kind}/${story.name} correctly`, async () => {
      await page.goto(`http://localhost:6006/iframe.html?id=${story.id}`);
      const root = await page.$('#root');
      if (null === root) return;

      const image = await root.screenshot();

      expect(image).toMatchImageSnapshot();
    });
  });
});
