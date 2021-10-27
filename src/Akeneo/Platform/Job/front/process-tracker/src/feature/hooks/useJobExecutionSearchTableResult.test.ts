import {useJobExecutionSearchTableResult} from './useJobExecutionSearchTableResult';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionSearchTableResult} from '../models/JobExecutionSearchTableResult';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobExecutionSearchTableResult: JobExecutionSearchTableResult = {
  items: [],
  matches_count: 0,
  total_count: 0,
};

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionSearchTableResult,
  }));
});

test('It fetches job search result', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionSearchTableResult());
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(expectedFetchedJobExecutionSearchTableResult);
});

test('It returns search job result only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionSearchTableResult());

  unmount();

  const jobSearchResult = result.current;

  expect(jobSearchResult).toEqual(null);
});
