import {useAvailableTargetsFetcher} from './useAvailableTargetsFetcher';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

test('It fetch the available target', async () => {
  const response = {};
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result} = renderHookWithProviders(() => useAvailableTargetsFetcher('search', 'fr_FR'));
  const availableTargetsFetcher = result.current;

  availableTargetsFetcher({
    system: 1,
    attribute: 2,
  });

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_get_grouped_targets_action', {
    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
  });
});
