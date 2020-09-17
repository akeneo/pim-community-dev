import * as Exports from './index';
import fs from 'fs';

const getSubfolders = (paths: string[]) =>
  paths.reduce(
    (folders: string[], path: string) => [
      ...folders,
      ...fs
        .readdirSync(path, {withFileTypes: true})
        .filter(file => file.isDirectory())
        .map(folder => folder.name),
    ],
    []
  );

const getIcons = () =>
  fs
    .readdirSync('src/icons')
    .filter(file => 'tsx' === file.split('.').pop())
    .map(file => file.split('.')[0]);

describe('Every module is exported correctly', () => {
  const exportNames = Object.keys(Exports);
  const components = [...getSubfolders(['src/components']), ...getIcons()];

  components.forEach(component => {
    it(`Test ${component} is exported correctly.
    If this test is failing, export ${component} component in src/index.ts`, () =>
      expect(exportNames).toContain(component));
  });
});
