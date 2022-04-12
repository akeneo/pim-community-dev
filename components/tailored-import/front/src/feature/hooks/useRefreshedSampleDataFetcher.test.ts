import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useRefreshedSampleDataFetcher} from './useRefreshedSampleDataFetcher';

test('it return refreshed sample data', async () => {
  const response = {refreshed_data: 'sample4'};
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result} = renderHookWithProviders(() => useRefreshedSampleDataFetcher());
  const refreshedSampleDataFetcher = result.current;
  const refreshedData = await refreshedSampleDataFetcher(
    '/file_key',
    ['sample1', 'sample2', 'sample3'],
    2,
    'sheet_1',
    2
  );

  expect(refreshedData).toEqual('sample4');
  expect(global.fetch).toBeCalledWith('pimee_tailored_import_get_refreshed_sample_data_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});

test('it return an error when cannot refresh sample data', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
  }));

  const {result} = renderHookWithProviders(() => useRefreshedSampleDataFetcher());
  const refreshedSampleDataFetcher = result.current;

  await expect(async () => {
    await refreshedSampleDataFetcher('/file_key', ['sample1', 'sample2', 'sample3'], 2, 'sheet_1', 2);
  }).rejects.toBeUndefined();
});
