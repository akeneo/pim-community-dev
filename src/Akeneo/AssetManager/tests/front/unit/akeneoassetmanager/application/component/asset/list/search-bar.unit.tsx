import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {render} from '@testing-library/react';
import '@testing-library/jest-dom/extend-expect';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import SearchBar, {useChannels} from 'akeneoassetmanager/application/component/asset/list/search-bar';
import {act} from 'react-dom/test-utils';
import {renderHook} from '@testing-library/react-hooks';
import userEvent from '@testing-library/user-event';

const emptyDataProvider = {channelFetcher: {fetchAll: () => new Promise(() => {})}};

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});

test('It displays a search input with an initialized value', async () => {
  const expectedSearchValue = 'SEARCH VALUE';
  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
        <SearchBar
          dataProvider={emptyDataProvider}
          searchValue={expectedSearchValue}
          context={{}}
          resultCount={0}
          onSearchChange={() => {}}
          onContextChange={() => {}}
        />
      </ThemeProvider>,
      container
    );
  });

  expect(container.querySelector('input').value).toEqual(expectedSearchValue);
});

test('It triggers the onSearchChange when the search field changes', async () => {
  jest.useFakeTimers();

  let actualValue = '';
  await act(async () => {
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
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
      </ThemeProvider>,
      container
    );
  });

  const expectedValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = container.querySelector('input');
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
    ReactDOM.render(
      <ThemeProvider theme={akeneoTheme}>
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
      </ThemeProvider>,
      container
    );
  });

  const expectedValue = 'SOME NEW SEARCH CRITERIA';
  const searchInput = container.querySelector('input');
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
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <SearchBar
        dataProvider={emptyDataProvider}
        searchValue={''}
        context={{}}
        resultCount={expectedResultCount}
        onSearchChange={() => {}}
        onContextChange={() => {}}
      />
    </ThemeProvider>
  );

  expect(getByText('pim_asset_manager.result_counter')).toBeInTheDocument();
});

test('It does not load channels on first rendering', async () => {
  const mockedDataProvider = {channelFetcher: {fetchAll: () => new Promise(() => {})}};

  const {result} = renderHook(() => useChannels(mockedDataProvider.channelFetcher));

  expect(result.current).toEqual([]);
});

// To activate once we figure out how to test hooks fetching data
// test('It selects the first locale in the channel, if the current locale does not exist for the current channel', () => {
//   const invalidContext = {channel: 'ecommerce', locale: 'unknown_locale_for_ecommerce'};
//   const expectedLabel = 'English (United States)';
//   const localesForChannels = [
//     {
//       code: 'ecommerce',
//       locales: [
//         {
//           code: 'en_US',
//           label: expectedLabel,
//           region: 'United States',
//           language: 'English',
//         },
//         {
//           code: 'fr_FR',
//           label: 'French (France)',
//           region: 'France',
//           language: 'French',
//         },
//       ],
//     },
//   ];

//   const {getByText} = render(
//     <ThemeProvider theme={akeneoTheme}>
//       <SearchBar
//         dataProvider={{channelFetcher: {fetchAll: () => new Promise(resolve => resolve(localesForChannels))}}}
//         searchValue={''}
//         context={invalidContext}
//         resultCount={0}
//         onSearchChange={() => {}}
//         onContextChange={() => {}}
//       />
//     </ThemeProvider>
//   );

//   expect(getByText(expectedLabel)).toBeInTheDocument();
// });
