import {useJobExecutionTable} from './useJobExecutionTable';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {JobExecutionTable, getDefaultJobExecutionFilter} from '../models';
import {act} from '@testing-library/react-hooks';

const expectedFetchedJobExecutionTable: JobExecutionTable = {
  rows: [],
  matches_count: 0,
};

let mockedDocumentVisibility = true;
jest.mock('@akeneo-pim-community/shared/lib/hooks/useDocumentVisibility', () => ({
  useDocumentVisibility: (): boolean => mockedDocumentVisibility,
}));

beforeEach(() => {
  mockedDocumentVisibility = true;
  global.fetch = jest.fn().mockImplementation(async () => ({
    json: async () => expectedFetchedJobExecutionTable,
  }));
});

test('It fetches job execution table', async () => {
  const defaultFilter = getDefaultJobExecutionFilter();
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(defaultFilter));
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
  const defaultFilter = getDefaultJobExecutionFilter();
  const {result, unmount} = renderHookWithProviders(() => useJobExecutionTable(defaultFilter));

  unmount();

  const [jobExecutionTable] = result.current;

  expect(jobExecutionTable).toEqual(null);
});

test('It does not fetch a job execution table while the previous fetch is not finished', async () => {
  jest.useFakeTimers();
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
  const {result} = renderHookWithProviders(() => useJobExecutionTable(filter));

  expect(global.fetch).toHaveBeenCalledTimes(1);

  const [jobExecutionTable, refreshJobExecutionTable] = result.current;
  expect(jobExecutionTable).toBeNull();
  expect(refreshJobExecutionTable).not.toBeNull();

  await act(async () => {
    await refreshJobExecutionTable();
  });
  expect(global.fetch).toHaveBeenCalledTimes(1);
});

test('It automatically refreshes the job execution table', async () => {
  jest.useFakeTimers();

  const filter = getDefaultJobExecutionFilter();
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(filter));

  await act(async () => {
    await waitForNextUpdate();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);

  await act(async () => {
    jest.advanceTimersByTime(5000);
    await waitForNextUpdate();
  });

  expect(global.fetch).toHaveBeenCalledTimes(2);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);
});

test('It does not automatically refresh the job execution table when told', async () => {
  jest.useFakeTimers();

  const filter = getDefaultJobExecutionFilter();
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(filter, false));

  await act(async () => {
    await waitForNextUpdate();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);

  await act(async () => {
    jest.advanceTimersByTime(5000);
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);
});

test('It does not refresh the job execution table when document is not visible', async () => {
  jest.useFakeTimers();
  mockedDocumentVisibility = false;

  const filter = getDefaultJobExecutionFilter();
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useJobExecutionTable(filter));

  await act(async () => {
    jest.advanceTimersByTime(1500);
    await waitForNextUpdate();
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
  expect(result.current[0]).toBe(expectedFetchedJobExecutionTable);

  await act(async () => {
    jest.advanceTimersByTime(5000);
  });

  expect(global.fetch).toHaveBeenCalledTimes(1);
});
