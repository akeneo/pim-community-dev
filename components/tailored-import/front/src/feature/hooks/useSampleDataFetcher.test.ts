import {useSampleDataFetcher} from './useSampleDataFetcher';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

test('it try to fetch sample data', async () => {
  const response = ['produit_1', 'produit_2', 'produit_3'];
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result} = renderHookWithProviders(() => useSampleDataFetcher());
  const sampleDataFetcher = result.current;

  await sampleDataFetcher('/file_key', 2, 'sheet_1', 2);

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_get_sample_data_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});
