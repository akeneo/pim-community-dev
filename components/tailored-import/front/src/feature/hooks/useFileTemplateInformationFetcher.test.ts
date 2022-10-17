import {useFileTemplateInformationFetcher} from './useFileTemplateInformationFetcher';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

test('It fetch the template information', async () => {
  const response = {};
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result} = renderHookWithProviders(() => useFileTemplateInformationFetcher());
  const fileTemplateInformationFetcher = result.current;

  fileTemplateInformationFetcher('path/to/foo.xlsx', null);

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_get_file_template_information_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  });
});
