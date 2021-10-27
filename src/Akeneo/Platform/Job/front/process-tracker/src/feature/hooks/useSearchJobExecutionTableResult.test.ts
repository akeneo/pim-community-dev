import {useSearchJobExecutionTableResult} from './useSearchJobExecutionTableResult';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {SearchJobExecutionTableResult} from '../models/SearchJobExecutionTableResult';
import {act} from '@testing-library/react-hooks';

const expectedFetchedSearchJobExecutionTableResult: SearchJobExecutionTableResult = {
  items: [],
  matches_count: 0,
  total_count: 0,
};

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedSearchJobExecutionTableResult,
  }));
});

test('It fetches search job execution table result', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useSearchJobExecutionTableResult());
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(expectedFetchedSearchJobExecutionTableResult);
});

test('It returns search job execution table result only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useSearchJobExecutionTableResult());

  unmount();

  const jobSearchResult = result.current;

  expect(jobSearchResult).toEqual(null);
});
