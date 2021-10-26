import {useJobSearchResult} from './useJobSearchResult';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {JobSearchResult} from '../models/JobSearchResult';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobSearchResult: JobSearchResult = {
  items: [],
  matches_count: 0,
  total_count: 0,
};

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobSearchResult,
  }));
});

test('It fetches job search result', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobSearchResult());
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(expectedFetchedJobSearchResult);
});

test('It returns search job result only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useJobSearchResult());

  unmount();

  const jobSearchResult = result.current;

  expect(jobSearchResult).toEqual(null);
});
