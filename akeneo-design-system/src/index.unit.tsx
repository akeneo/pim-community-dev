import * as Exports from './index';
import fs from 'fs';
import '@testing-library/jest-dom/extend-expect';
import {render} from 'storybook/test-util';
import React from 'react';

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
        If this test is failing, export "${component}" component in src/index.ts`, () =>
      expect(exportNames).toContain(component));
  });
});

describe('Every module should support forwardRef', () => {
  const components = getSubfolders(['src/components']);

  components.forEach(component => {
    it(`Test ${component} support forwardRef.
        If this test is failing, add forwardRef support to the "${component}" component`, () => {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-var-requires
      const module = require(`./components/${component}/${component}.tsx`);
      // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment, @typescript-eslint/no-unsafe-member-access
      const Component = module[component];

      const ref = {current: null};
      render(<Component ref={ref} />);
      expect(ref.current).not.toBe(null);
    });
  });
});
