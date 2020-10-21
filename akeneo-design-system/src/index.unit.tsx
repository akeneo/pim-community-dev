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

const getFiles = (path: string) =>
  fs
    .readdirSync(path)
    .filter(file => 'tsx' === file.split('.').pop())
    .map(file => file.split('.')[0]);

describe('Every module is exported correctly', () => {
  const exportNames = Object.keys(Exports);
  const components = [...getSubfolders(['src/components']), ...getFiles('src/icons'), ...getFiles('src/illustrations')];

  test.each(components)(
    `Test %s is exported correctly.
    If this test is failing, export the Component in src/index.ts`,
    componentName => expect(exportNames).toContain(componentName)
  );
});

describe('Every Component should support forwardRef', () => {
  test.each(Object.keys(Components))(
    `Test %s support forwardRef.
    If this test is failing, add forwardRef support to the Component`,
    component => {
      const Component = Components[component] as ForwardRefRenderFunction<Element, PropsWithRef<any>>;
      const ref = {current: null};

      render(<Component ref={ref} />);
      expect(ref.current).not.toBe(null);
    }
  );
});

describe('Every Component should support ...rest props', () => {
  test.each(Object.keys(Components))(
    `Test %s support ...rest props.
    If this test is failing, add ...rest prop support on the Component`,
    component => {
      const Component = Components[component] as FC;

      const {container} = render(<Component data-my-attribute="my_value" />);
      expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
    }
  );
});
