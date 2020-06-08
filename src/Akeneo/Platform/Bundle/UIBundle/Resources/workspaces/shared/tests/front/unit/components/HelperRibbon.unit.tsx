import React from 'react';
import ReactDOM from 'react-dom';
import {act, getByTitle} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider, HelperRibbon, HelperLevel} from '@akeneo-pim-community/shared';

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It displays an info ribbon', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <HelperRibbon level={HelperLevel.HELPER_LEVEL_INFO}></HelperRibbon>
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByTitle(container, 'Info')).toBeInTheDocument();
});

test('It displays an error ribbon', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <HelperRibbon level={HelperLevel.HELPER_LEVEL_ERROR}></HelperRibbon>
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByTitle(container, 'Warning')).toBeInTheDocument();
});
test('It displays a warning ribbon', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <HelperRibbon level={HelperLevel.HELPER_LEVEL_WARNING}></HelperRibbon>
      </AkeneoThemeProvider>,
      container
    );
  });

  expect(getByTitle(container, 'Warning')).toBeInTheDocument();
});
