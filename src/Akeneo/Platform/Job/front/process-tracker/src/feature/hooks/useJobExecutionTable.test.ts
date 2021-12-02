import {useJobExecutionTable} from './useJobExecutionTable';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionTable, getDefaultJobExecutionFilter} from '../models';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobExecutionTable: JobExecutionTable = {
  rows: [],
  matches_count: 0,
};

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionTable,
  }));
});

test('It fetches job execution table', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() =>
    useJobExecutionTable(getDefaultJobExecutionFilter())
  );
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current[0]).toEqual(expectedFetchedJobExecutionTable);
});

test('It can refresh job execution table', async () => {
  const defaultFilter = getDefaultJobExecutionFilter();
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(defaultFilter));

  await act(async () => {
    await waitForNextUpdate();
  });

  expect(global.fetch).toBeCalledTimes(1);
  const [jobExecutionTable, refreshJobExecutionTable] = result.current;

  expect(jobExecutionTable).toEqual(expectedFetchedJobExecutionTable);
  await act(async () => {
    await refreshJobExecutionTable();
  });

  expect(global.fetch).toBeCalledTimes(2);
});

test('It returns job execution table only if hook is mounted', async () => {
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionTable(getDefaultJobExecutionFilter()));

  unmount();

  const [jobExecutionTable] = result.current;

  expect(jobExecutionTable).toEqual(null);
});

test.only('It does not fetch a job execution table while the previous fetch is not finished', async () => {
  global.fetch = jest.fn().mockImplementation(
    async () =>
      new Promise(resolve =>
        setTimeout(
          () =>
            resolve({
              ok: true,
              json: async () => expectedFetchedJobExecutionTable,
            }),
          1500
        )
      )
  );

  const filter = getDefaultJobExecutionFilter();

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(filter));
  await act(async () => {
    await waitForNextUpdate();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);

  const [jobExecutionTable, refreshJobExecutionTable] = result.current;
  expect(jobExecutionTable).toEqual(expectedFetchedJobExecutionTable);
  expect(refreshJobExecutionTable).not.toBeNull();

  await act(async () => {
    await refreshJobExecutionTable();
  });
  expect(global.fetch).toHaveBeenCalledTimes(1);
});
