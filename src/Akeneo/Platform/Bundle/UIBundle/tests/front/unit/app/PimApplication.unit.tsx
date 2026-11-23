import React from 'react';
import ReactDOM from 'react-dom';
import {act} from 'react-dom/test-utils';
import {PimApplication} from '../../../../Resources/public/js/app/PimApplication';
import {LegacyView} from '../../../../Resources/public/js/app/bootstrap';

jest.mock('../../../../Resources/public/js/app/bootstrap', () => ({}));

const legacyView = (code: string, render: () => unknown): LegacyView => ({
  el: document.createElement('div'),
  code,
  render: jest.fn(render),
  remove: jest.fn(),
});

let root: HTMLElement;
beforeEach(() => {
  root = document.createElement('div');
  document.body.appendChild(root);
  jest.spyOn(console, 'error').mockImplementation(() => {});
});
afterEach(() => {
  ReactDOM.unmountComponentAtNode(root);
  root.remove();
  jest.restoreAllMocks();
});

test('it renders the shell zones then calls onReady', () => {
  const menu = legacyView('menu', () => {});
  const onReady = jest.fn(() => {
    expect(menu.render).toHaveBeenCalled();
  });

  act(() => {
    ReactDOM.render(<PimApplication zones={{menu: [menu]}} onReady={onReady} />, root);
  });

  expect(onReady).toHaveBeenCalledTimes(1);
  expect(root.querySelector('[data-drop-zone="menu"]')?.contains(menu.el)).toBe(true);
  expect(root.querySelector('#container')).not.toBeNull();
});

test('it keeps the application mounted when the first page fails to render', () => {
  const error = new Error('page error');

  act(() => {
    ReactDOM.render(
      <PimApplication
        zones={{}}
        onReady={() => {
          throw error;
        }}
      />,
      root
    );
  });

  expect(root.querySelector('#page')).not.toBeNull();
  expect(root.querySelector('#container')).not.toBeNull();
  expect(console.error).toHaveBeenCalledWith(error);
});

test('it keeps the application and the other shell views when a shell view fails to render', () => {
  const failing = legacyView('failing', () => {
    throw new Error('extension error');
  });
  const menu = legacyView('menu', () => {});
  const onReady = jest.fn();

  act(() => {
    ReactDOM.render(<PimApplication zones={{menu: [failing, menu]}} onReady={onReady} />, root);
  });

  expect(menu.render).toHaveBeenCalled();
  expect(onReady).toHaveBeenCalledTimes(1);
  expect(root.querySelector('#page')).not.toBeNull();
});
