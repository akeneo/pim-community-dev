import fs from 'fs';
import * as Exports from './index';

const EXCLUDE = ['Input'];

const getSubfolders = (paths: string[]) =>
  paths.reduce(
    (folders: string[], path: string) => [
      ...folders,
      ...fs
        .readdirSync(path, {withFileTypes: true})
        .filter(
          directory =>
            !EXCLUDE.includes(directory.name) &&
            directory.isDirectory() &&
            fs.readdirSync(path + '/' + directory.name, {withFileTypes: true}).some(file => file.isFile())
        )
        .map(directory => directory.name),
    ],
    []
  );

const getFiles = (path: string) =>
  fs
    .readdirSync(path)
    .filter(file => 'tsx' === file.split('.').pop())
    .map(file => file.split('.')[0]);

describe('Every module is exported correctly', () => {
  const exportNames = Object.keys(Exports);
  const components = [
    ...getSubfolders(['src/components', 'src/components/Input']),
    ...getFiles('src/icons'),
    ...getFiles('src/illustrations'),
  ];

  test.each(components)(
    `Test %s is exported correctly.
    If this test is failing, export the Component in src/index.ts`,
    componentName => expect(exportNames).toContain(componentName)
  );
});
