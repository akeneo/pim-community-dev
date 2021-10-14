import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {SearchBar} from 'akeneoassetmanager/application/component/asset/list/search-bar';
import {act} from 'react-dom/test-utils';
import {renderHook} from '@testing-library/react-hooks';
import userEvent from '@testing-library/user-event';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';

const emptyDataProvider = {channelFetcher: {fetchAll: () => new Promise(jest.fn())}};

test('It displays a search input with an initialized value', async () => {
  const expectedSearchValue = 'SEARCH VALUE';

  renderWithProviders(
    <SearchBar
      dataProvider={emptyDataProvider}
      searchValue={expectedSearchValue}
      context={{}}
      resultCount={0}
      onSearchChange={jest.fn()}
      onContextChange={jest.fn()}
    />
  );

  expect(screen.getByDisplayValue(expectedSearchValue)).toBeInTheDocument();
});

test('It triggers the onSearchChange when the search field changes', async () => {
  jest.useFakeTimers();
  const handleSearchChange = jest.fn();

  renderWithProviders(
    <SearchBar
      dataProvider={emptyDataProvider}
      searchValue={''}
      context={{}}
      resultCount={0}
      onSearchChange={handleSearchChange}
      onContextChange={jest.fn()}
    />
  );

  const expectedValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search');
  userEvent.type(searchInput, expectedValue);

  act(() => {
    jest.runAllTimers();
  });

  expect(handleSearchChange).toHaveBeenCalledWith(expectedValue);
});

test('It triggers the onSearchChange when the search field is emptied', async () => {
  jest.useFakeTimers();
  const handleSearchChange = jest.fn();

  renderWithProviders(
    <SearchBar
      dataProvider={emptyDataProvider}
      searchValue={''}
      context={{}}
      resultCount={0}
      onSearchChange={handleSearchChange}
      onContextChange={jest.fn()}
    />
  );

  const expectedValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search');
  userEvent.type(searchInput, expectedValue);
  act(() => {
    jest.runAllTimers();
  });

  expect(handleSearchChange).toHaveBeenCalledWith(expectedValue);
  userEvent.clear(searchInput);
  act(() => {
    jest.runAllTimers();
  });

  expect(handleSearchChange).toBeCalledWith('');
});

test('It displays a result count', () => {
  const expectedResultCount = 10;
  renderWithProviders(
    <SearchBar
      dataProvider={emptyDataProvider}
      searchValue=""
      context={{}}
      resultCount={expectedResultCount}
      onSearchChange={jest.fn()}
      onContextChange={jest.fn()}
    />
  );

  expect(screen.getByText('pim_asset_manager.result_counter')).toBeInTheDocument();
});

test('It does not load channels on first rendering', () => {
  const mockedDataProvider = {channelFetcher: {fetchAll: () => new Promise(jest.fn())}};

  const {result} = renderHook(() => useChannels(mockedDataProvider.channelFetcher));

  expect(result.current).toEqual([]);
});
