import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {usePimVersion} from '@akeneo-pim-community/activity/src/hooks/usePimVersion';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
      $: any;
    }
  }
}

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;

const mockFetch = (isAnalyticsWanted: boolean) =>
  jest.fn().mockImplementation(async (url: string) => {
    if (url.includes('pim_dashboard_version_data')) {
      return {
        json: async () => ({
          version: 'CE 2026.5 Sahara Hare',
          analytics_url: 'https://updates.akeneo.com/CE-2026.5.json',
          is_analytics_wanted: isAnalyticsWanted,
        }),
      };
    }
    if (url.includes('pim_analytics_data_collect')) {
      return {json: async () => ({pim_edition: 'CE', pim_version: '2026.5'})};
    }

    return {status: 200, json: async () => ({last_patch: {name: 'v2026.6'}})};
  });

beforeEach(() => {
  sessionStorage.clear();
  global.$ = {param: (data: object) => new URLSearchParams(data as Record<string, string>).toString()};
});

afterEach(() => {
  delete global.fetch;
  delete global.$;
});

test('it sends the collected data to the tracker from the browser and returns the version', async () => {
  global.fetch = mockFetch(true);

  const {result, waitFor} = renderHook(() => usePimVersion(), {wrapper});

  await waitFor(() => expect(sessionStorage.getItem('analytics-called')).toBe('1'));

  expect(global.fetch).toHaveBeenCalledWith(
    'https://updates.akeneo.com/CE-2026.5.json?pim_edition=CE&pim_version=2026.5'
  );
  expect(result.current).toEqual({version: 'CE 2026.5 Sahara Hare'});
});

test('it does not call the tracker twice in the same session', async () => {
  sessionStorage.setItem('analytics-called', '1');
  global.fetch = mockFetch(true);

  const {result, waitFor} = renderHook(() => usePimVersion(), {wrapper});

  await waitFor(() => expect(result.current.version).toBe('CE 2026.5 Sahara Hare'));

  expect(global.fetch).toHaveBeenCalledTimes(1);
});

test('it does not call the tracker when analytics are not wanted', async () => {
  global.fetch = mockFetch(false);

  const {result, waitFor} = renderHook(() => usePimVersion(), {wrapper});

  await waitFor(() => expect(result.current.version).toBe('CE 2026.5 Sahara Hare'));

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(sessionStorage.getItem('analytics-called')).toBeNull();
});
