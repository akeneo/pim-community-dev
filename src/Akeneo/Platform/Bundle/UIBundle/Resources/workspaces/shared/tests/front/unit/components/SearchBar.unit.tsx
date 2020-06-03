import React from 'react';
import ReactDOM from 'react-dom';
import {act, getByTitle, fireEvent} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {AkeneoThemeProvider, SearchBar} from '@akeneo-pim-community/shared';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It displays input errors', async () => {
  const onSearchChange = jest.fn();
  await act(async () => {
    ReactDOM.render(
      <DependenciesProvider>
        <AkeneoThemeProvider>
          <SearchBar onSearchChange={onSearchChange} searchValue={'hey'} count={12} />
        </AkeneoThemeProvider>{' '}
      </DependenciesProvider>,
      container
    );
  });

  const input = getByTitle(container, 'pim_common.search') as HTMLInputElement;

  fireEvent.change(input, {target: {value: 'hey!'}});

  expect(onSearchChange).toBeCalledWith('hey!');
});
