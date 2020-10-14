import React, {FC, ForwardRefRenderFunction, PropsWithRef} from 'react';
import * as Exports from './index';
import * as Components from './components';
import fs from 'fs';
import '@testing-library/jest-dom/extend-expect';
import {render} from 'storybook/test-util';

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
    test(`Test ${component} is exported correctly.
        If this test is failing, export "${component}" component in src/index.ts`, () => {
      expect(exportNames).toContain(component);
    });
  });
});

describe('Every module should support forwardRef', () => {
  Object.keys(Components).forEach(component => {
    test(`Test ${component} support forwardRef.
        If this test is failing, add forwardRef support to the "${component}" component`, () => {
      const Component = Components[component] as ForwardRefRenderFunction<Element, PropsWithRef<any>>;
      const ref = {current: null};

      render(<Component ref={ref} />);
      expect(ref.current).not.toBe(null);
    });
  });
});

describe('Every module should support ...rest props', () => {
  Object.keys(Components).forEach(component => {
    test(`Test ${component} support ...rest props.
        If this test is failing, add ...rest support on props to the "${component}" component`, () => {
      const Component = Components[component] as FC;

      const {container} = render(<Component data-my-attribute="my_value" />);
      expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
    });
  });
});
