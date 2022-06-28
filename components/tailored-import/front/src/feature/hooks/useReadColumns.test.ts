import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {Column, FileStructure} from 'feature/models';
import {useReadColumns} from './useReadColumns';

const fileStructure: FileStructure = {
  header_row: 0,
  first_column: 0,
  first_product_row: 1,
  sheet_name: null,
  unique_identifier_column: 0,
};

const mockedColumns: Column[] = [{label: 'sku', index: 0, uuid: 'fake-uuid'}];

test('It fetches columns', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => mockedColumns,
  }));

  const {result} = renderHookWithProviders(() => useReadColumns());
  const readColumns = result.current;

  readColumns('file-key', fileStructure);

  expect(global.fetch).toBeCalledWith('pimee_tailored_import_read_columns_action', {
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({file_key: 'file-key', file_structure: fileStructure}),
    method: 'POST',
  });
});
