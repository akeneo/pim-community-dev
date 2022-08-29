import {useAvailableSourcesFetcher} from './useAvailableSourcesFetcher';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

test('It fetch the available source', async () => {
  const response = {};
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result} = renderHookWithProviders(() => useAvailableSourcesFetcher('search', 'fr_FR'));
  const availableSourcesFetcher = result.current;

  await availableSourcesFetcher({
    attribute: 0,
    system: 0,
    association_type: 0,
  });

  expect(global.fetch).toBeCalledWith('pimee_tailored_export_get_product_grouped_sources_action', {
    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
  });
});
