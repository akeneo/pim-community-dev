import 'expect-puppeteer';
import fs from 'fs';
import {toMatchImageSnapshot} from 'jest-image-snapshot';

expect.extend({toMatchImageSnapshot});

const EXCLUDE = ['Components/Modal', 'Components/Dropdown', 'Patterns/Overlays'];

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
const stories = Object.values(storiesDump.stories)
  .filter(
    story =>
      (0 === story.id.indexOf('components') || 0 === story.id.indexOf('patterns')) && !EXCLUDE.includes(story.kind)
  )
  .map(story => [story.kind, story.name, story.id]);

describe('Visual tests', () => {
  test.each(stories)('Renders %s/%s correctly', async (_kind, _name, id) => {
    await page.goto(`http://localhost:6006/iframe.html?id=${id}`);
    const root = await page.$('#root');
    if (null === root) return;

    await new Promise(resolve => setTimeout(resolve, 500));
    const image = await root.screenshot();

    expect(image).toMatchImageSnapshot({
      failureThreshold: 1,
      failureThresholdType: 'percent',
    });
  });
});
