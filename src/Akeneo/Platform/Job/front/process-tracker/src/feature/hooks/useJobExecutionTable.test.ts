import {useJobExecutionTable} from './useJobExecutionTable';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionTable} from '../models/JobExecutionTable';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobExecutionTable: JobExecutionTable = {
  rows: [],
  matches_count: 0,
  total_count: 0,
};

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionTable,
  }));
});

test('It fetches job execution table', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(1));
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(expectedFetchedJobExecutionTable);
});

test('It returns job execution table only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionTable(1));

  unmount();

  const jobExecutionTable = result.current;

  expect(jobExecutionTable).toEqual(null);
});
