'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useFetchResult} from 'akeneoassetmanager/application/hooks/grid';
import {renderHook, act} from '@testing-library/react-hooks';

const createQuery = function() {
  return {...arguments};
};
const firstSearchResults = {
  matchesCount: 70,
  totalCount: 100,
  items: [{MY_NICE: 'ITEM'}],
};
const secondSearchResults = {
  matchesCount: 70,
  totalCount: 100,
  items: [{MY_OTHER_NICE: 'ITEM'}],
};
const thirdSearchResults = {
  matchesCount: 30,
  totalCount: 100,
  items: [{MY_OTHER_NICE: 'ITEM'}],
};
const dataProvider = {
  assetFetcher: {
    search: query =>
      new Promise(resolve => {
        act(() => {
          if (query.size) {
            setTimeout(() => resolve(secondSearchResults), 20);
          } else if (query['0'] === 'SMALL_ASSET_FAMILY_IDENTIFIER') {
            setTimeout(() => resolve(thirdSearchResults), 20);
          } else {
            setTimeout(() => resolve(firstSearchResults), 20);
          }
        });
      }),
  },
};

describe('Test grid fetching hook', () => {
  test('I can fetch the first batch of results', async () => {
    let currentResults = null;

    renderHook(() =>
      useFetchResult(createQuery)(
        true,
        dataProvider,
        'ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        receivedSearchResults => {
          currentResults = receivedSearchResults;
        }
      )
    );

    expect(currentResults).toEqual(null);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(firstSearchResults);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(secondSearchResults);
  });

  test('It does not do anything if the asset family identifier is null', async () => {
    let currentResults = null;

    renderHook(() =>
      useFetchResult(createQuery)(
        true,
        dataProvider,
        null,
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        receivedSearchResults => {
          currentResults = receivedSearchResults;
        }
      )
    );

    expect(currentResults).toEqual(null);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(null);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(null);
  });

  test('I can ask for a search result reload', async () => {
    let currentResults = null;

    const {result} = renderHook(() =>
      useFetchResult(createQuery)(
        true,
        dataProvider,
        'ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        receivedSearchResults => {
          currentResults = receivedSearchResults;
        }
      )
    );

    expect(currentResults).toEqual(null);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(firstSearchResults);
    act(() => result.current());

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });
    expect(currentResults).toEqual(firstSearchResults);
  });

  test('It only fetches the first page if there is only one page', async () => {
    let currentResults = null;

    renderHook(() =>
      useFetchResult(createQuery)(
        true,
        dataProvider,
        'SMALL_ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        receivedSearchResults => {
          currentResults = receivedSearchResults;
        }
      )
    );

    expect(currentResults).toEqual(null);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(thirdSearchResults);

    await new Promise(resolve => {
      setTimeout(resolve, 30);
    });

    expect(currentResults).toEqual(thirdSearchResults);
  });
});
