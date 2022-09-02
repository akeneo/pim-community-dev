import {act} from '@testing-library/react-hooks';
import {Column, createAttributeDataMapping, createPropertyDataMapping, DataMapping} from 'feature/models';
import {renderHookWithProviders} from 'feature/tests';
import {useSearchDataMappings} from './useSearchDataMappings';

const getAttributeDataMapping = (code: string, sources: string[]) => ({
  ...createAttributeDataMapping(
    {
      code,
      localizable: false,
      scopable: false,
      is_locale_specific: false,
      available_locales: [],
      type: 'pim_catalog_text',
      labels: {},
    },
    []
  ),
  sources,
});

const columns: Column[] = [
  {uuid: 'uuid-IDEnTiFier', index: 0, label: 'IDEnTiFier'},
  {uuid: 'uuid-Name', index: 1, label: 'Name'},
  {uuid: 'uuid-idendescrip tion', index: 2, label: 'idendescrip tion'},
  {uuid: 'uuid-catego1', index: 2, label: 'catego1'},
  {uuid: 'uuid-catego2', index: 2, label: 'catego2 tion'},
];

const identifierDataMapping = getAttributeDataMapping('sku', ['uuid-IDEnTiFier']);
const nameDataMapping = getAttributeDataMapping('name', ['uuid-Name']);
const descriptionDataMapping = getAttributeDataMapping('description', ['uuid-idendescrip tion']);
const categoriesDataMapping = {...createPropertyDataMapping('categories'), sources: ['uuid-catego1', 'uuid-catego2']};

const dataMappings: DataMapping[] = [
  identifierDataMapping,
  nameDataMapping,
  descriptionDataMapping,
  categoriesDataMapping,
];

test('it can search data mappings based on target and column labels', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => ['sku'],
  }));

  const {result, rerender} = renderHookWithProviders(
    ({search, columns, dataMappings}) => useSearchDataMappings(dataMappings, columns, search),
    {
      search: '',
      dataMappings,
      columns,
    }
  );

  expect(result.current).toEqual(dataMappings);
  expect(global.fetch).not.toHaveBeenCalled();

  await act(async () => void rerender({search: 'iden', dataMappings, columns}));
  expect(result.current).toEqual([identifierDataMapping, descriptionDataMapping]);

  await act(async () => void rerender({search: 'cat', dataMappings, columns}));
  expect(result.current).toEqual([identifierDataMapping, categoriesDataMapping]);

  await act(async () => void rerender({search: 'TION', dataMappings, columns}));
  expect(result.current).toEqual([identifierDataMapping, descriptionDataMapping, categoriesDataMapping]);

  expect(global.fetch).toHaveBeenCalledTimes(3);
});

test('it does not set state when unmounted', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => ['sku'],
  }));

  const {result, rerender, unmount} = renderHookWithProviders(() =>
    useSearchDataMappings(dataMappings, columns, 'search')
  );

  expect(result.current).toEqual(dataMappings);

  await act(async () => {
    unmount();
    rerender({search: 'iden', dataMappings, columns});
  });

  expect(result.current).toEqual(dataMappings);
});
