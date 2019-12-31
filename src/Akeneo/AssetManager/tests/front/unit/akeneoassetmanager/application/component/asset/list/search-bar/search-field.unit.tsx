import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {ThemeProvider} from 'styled-components';
import SearchField from 'akeneoassetmanager/application/component/asset/list/search-bar/search-field';
import {render} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {act} from 'react-dom/test-utils';

test('It displays an empty search field', async () => {
  const {container, getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <SearchField value="" onChange={() => {}} />
    </ThemeProvider>
  );

  expect(container.querySelector('input').value).toEqual('');
  expect(getByText('Search')).toBeInTheDocument();
});

test('It displays a search value for the field', async () => {
  const searchCriteria = 'SOME SEARCH';
  const {container} = render(
    <ThemeProvider theme={akeneoTheme}>
      <SearchField value={searchCriteria} onChange={() => {}} />
    </ThemeProvider>
  );

  expect(container.querySelector('input').value).toEqual(searchCriteria);
});

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});
jest.useFakeTimers();
test('It calls the onChange callback when it is updated', async () => {
  let isTriggered = false;
  let actualValue = '';
  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <SearchField
          value={actualValue}
          onChange={newValue => {
            actualValue = newValue;
          }}
        />
      </ThemeProvider>,
      container
    );
  });

  const newValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = container.querySelector('input');
  await act(async () => {
    await userEvent.type(searchInput, newValue);
    jest.runAllTimers();
  });

  expect(actualValue).toEqual(newValue);
});
