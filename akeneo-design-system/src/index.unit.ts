import * as Exports from './index';
import fs from 'fs';

const getSubfolders = (paths: string[]) =>
  paths.reduce(
    (result: string[], path: string) => [
      ...result,
      ...fs
        .readdirSync(path, {withFileTypes: true})
        .filter(file => file.isDirectory())
        .map(component => component.name),
    ],
    []
  );

const getIcons = () =>
  fs
    .readdirSync('src/icons')
    .filter(file => 'tsx' === file.split('.').pop())
    .map(file => file.split('.')[0]);

describe('every module is exported correctly', () => {
  const exportNames = Object.keys(Exports);

  [...getSubfolders(['src/components']), ...getIcons()].forEach(component => {
    it(`Test ${component} is exported correctly.
    If this test failing, export ${component} component in the src/index.ts`, () =>
      expect(exportNames).toContain(component));
  });
});
