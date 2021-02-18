import React from 'react';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import SearchBar from 'akeneoassetmanager/application/component/asset/list/search-bar';
import {act} from 'react-dom/test-utils';
import {renderHook} from '@testing-library/react-hooks';
import userEvent from '@testing-library/user-event';
import {useChannels} from 'akeneoassetmanager/application/hooks/channel';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

const emptyDataProvider = {channelFetcher: {fetchAll: () => new Promise(() => {})}};

test('It displays a search input with an initialized value', async () => {
  const expectedSearchValue = 'SEARCH VALUE';
  await act(async () => {
    renderWithProviders(
      <SearchBar
        dataProvider={emptyDataProvider}
        searchValue={expectedSearchValue}
        context={{}}
        resultCount={0}
        onSearchChange={() => {}}
        onContextChange={() => {}}
      />
    );
  });

  expect(screen.getByDisplayValue(expectedSearchValue)).toBeInTheDocument();
});

test('It triggers the onSearchChange when the search field changes', async () => {
  jest.useFakeTimers();

  let actualValue = '';
  await act(async () => {
    renderWithProviders(
      <SearchBar
        dataProvider={emptyDataProvider}
        searchValue={''}
        context={{}}
        resultCount={0}
        onSearchChange={newValue => {
          actualValue = newValue;
        }}
        onContextChange={() => {}}
      />
    );
  });

  const expectedValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search');
  await act(async () => {
    await userEvent.type(searchInput, expectedValue);
    jest.runAllTimers();
  });

  expect(actualValue).toEqual(expectedValue);
});

test('It triggers the onSearchChange when the search field is emptied', async () => {
  jest.useFakeTimers();

  let actualValue = '';
  await act(async () => {
    renderWithProviders(
      <SearchBar
        dataProvider={emptyDataProvider}
        searchValue={''}
        context={{}}
        resultCount={0}
        onSearchChange={newValue => {
          actualValue = newValue;
        }}
        onContextChange={() => {}}
      />
    );
  });

  const expectedValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = screen.getByPlaceholderText('pim_asset_manager.asset.grid.search');
  await act(async () => {
    await userEvent.type(searchInput, expectedValue);
    jest.runAllTimers();
  });

  expect(actualValue).toEqual(expectedValue);

  await act(async () => {
    // https://github.com/testing-library/user-event/issues/182
    await userEvent.type(searchInput, '', {allAtOnce: true});
    jest.runAllTimers();
  });

  expect(actualValue).toEqual('');
});

test('It displays a result count', () => {
  const expectedResultCount = 10;
  renderWithProviders(
    <SearchBar
      dataProvider={emptyDataProvider}
      searchValue=""
      context={{}}
      resultCount={expectedResultCount}
      onSearchChange={() => {}}
      onContextChange={() => {}}
    />
  );

  expect(screen.getByText('pim_asset_manager.result_counter')).toBeInTheDocument();
});

test('It does not load channels on first rendering', () => {
  const mockedDataProvider = {channelFetcher: {fetchAll: () => new Promise(() => {})}};

  const {result} = renderHook(() => useChannels(mockedDataProvider.channelFetcher));

  expect(result.current).toEqual([]);
});
