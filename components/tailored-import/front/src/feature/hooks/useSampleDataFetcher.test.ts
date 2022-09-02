import {useSampleDataFetcher} from './useSampleDataFetcher';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

test('it return fetched sample data', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => ['produit_1', 'produit_2', 'produit_3'],
  }));

  const {result} = renderHookWithProviders(() => useSampleDataFetcher());
  const sampleDataFetcher = result.current;

  const sampleData = await sampleDataFetcher('/file_key', [2], 'sheet_1', 2);

  expect(sampleData).toEqual(['produit_1', 'produit_2', 'produit_3']);
  expect(global.fetch).toBeCalledWith('pimee_tailored_import_get_sample_data_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});

test('it return an error when cannot fetch sample data', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: false,
  }));

  const {result} = renderHookWithProviders(() => useSampleDataFetcher());
  const sampleDataFetcher = result.current;

  await expect(async () => {
    await sampleDataFetcher('/file_key', [1, 2], 'sheet_1', 2);
  }).rejects.toBeUndefined();
});
