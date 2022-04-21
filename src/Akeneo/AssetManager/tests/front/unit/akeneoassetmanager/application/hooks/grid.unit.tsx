'use strict';

import '@testing-library/jest-dom/extend-expect';
import {useFetchResult, createQuery} from 'akeneoassetmanager/application/hooks/grid';
import {emptySearchResult} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {renderHook, act} from '@testing-library/react-hooks';

const flushPromises = () => new Promise(setImmediate);

describe('Test grid fetching hook', () => {
  test('I can receive up to 500 results in two queries', async () => {
    const partialResults = {
      matchesCount: 51,
      totalCount: 100,
      items: [{foo: 'FOO'}],
    };
    const fullResults = {
      matchesCount: 51,
      totalCount: 100,
      items: [{foo: 'FOO'}, {bar: 'BAR'}],
    };
    const search = jest
      .fn()
      .mockImplementationOnce(query => Promise.resolve(partialResults))
      .mockImplementationOnce(query => Promise.resolve(fullResults));
    const handleReceivedSearchResults = jest.fn();

    renderHook(() =>
      useFetchResult(createQuery)(
        true,
        {assetFetcher: {search: search}},
        'ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        handleReceivedSearchResults
      )
    );

    await flushPromises();
    expect(handleReceivedSearchResults).toHaveBeenCalledTimes(2);
    expect(handleReceivedSearchResults).toHaveBeenCalledWith(partialResults);
    expect(handleReceivedSearchResults).toHaveBeenCalledWith(fullResults);
  });

  test('I can receive the results in one query if there is less than 50', async () => {
    const results = {
      matchesCount: 50,
      totalCount: 100,
      items: [],
    };

    const search = jest.fn().mockImplementationOnce(query => Promise.resolve(results));
    const handleReceivedSearchResults = jest.fn();

    renderHook(() =>
      useFetchResult(createQuery)(
        true,
        {assetFetcher: {search: search}},
        'ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        handleReceivedSearchResults
      )
    );

    await flushPromises();
    expect(handleReceivedSearchResults).toHaveBeenCalledTimes(1);
    expect(handleReceivedSearchResults).toHaveBeenCalledWith(results);
  });

  test('I abort fetch results if another query is launched', async () => {
    const results = {
      matchesCount: 50,
      totalCount: 100,
      items: [],
    };

    const search = jest.fn().mockImplementation(() => Promise.resolve(results));
    const handleReceivedSearchResults = jest.fn();

    const {rerender} = renderHook(() =>
      useFetchResult(createQuery)(
        true,
        {assetFetcher: {search: search}},
        'ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        handleReceivedSearchResults
      )
    );

    rerender();
    await flushPromises();
    expect(handleReceivedSearchResults).toHaveBeenCalledTimes(1);
    expect(handleReceivedSearchResults).toHaveBeenCalledWith(results);
  });

  test('It returns an empty result if the asset family identifier is null', async () => {
    const search = jest.fn();
    const handleReceivedSearchResults = jest.fn();

    renderHook(() =>
      useFetchResult(createQuery)(
        true,
        {assetFetcher: {search: search}},
        null,
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        handleReceivedSearchResults
      )
    );

    await flushPromises();
    expect(search).not.toHaveBeenCalled();
    expect(handleReceivedSearchResults).toHaveBeenCalledWith(emptySearchResult());
  });

  test('I can ask for a search result reload', async () => {
    const results = {
      matchesCount: 50,
      totalCount: 100,
      items: [],
    };

    const search = jest.fn().mockImplementation(query => Promise.resolve(results));
    const handleReceivedSearchResults = jest.fn();

    const {result} = renderHook(() =>
      useFetchResult(createQuery)(
        true,
        {assetFetcher: {search: search}},
        'ASSET_FAMILY_IDENTIFIER',
        [],
        'MY_SEARCH',
        ['EXCLUDED_ASSET_CODE'],
        {
          locale: 'en_US',
          channel: 'ecommerce',
        },
        handleReceivedSearchResults
      )
    );

    await flushPromises();
    expect(handleReceivedSearchResults).toHaveBeenCalledTimes(1);
    expect(handleReceivedSearchResults).toHaveBeenCalledWith(results);

    act(() => result.current());

    await flushPromises();
    expect(handleReceivedSearchResults).toHaveBeenCalledTimes(2);
  });
});
