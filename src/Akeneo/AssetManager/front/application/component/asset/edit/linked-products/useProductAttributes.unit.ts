import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useProductAttributes} from './useProductAttributes';

test('It fetches product attributes', async () => {
  const response = [
    {
      code: 'Test_asset',
      type: 'pim_catalog_asset_collection',
      useable_as_grid_filter: false,
      reference_data_name: 'atmosphere',
      labels: {en_US: 'Test asset'},
    },
    {
      code: 'Yolo',
      type: 'pim_catalog_asset_collection',
      useable_as_grid_filter: false,
      reference_data_name: 'atmosphere',
      labels: {en_US: 'Yolo'},
    },
  ];

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useProductAttributes('atmosphere'));

  await act(async () => {
    await waitForNextUpdate();
  });

  const [attributes, selectedAttribute] = result.current;
  expect(attributes).toEqual(response);
  expect(selectedAttribute).toEqual(response[0]);
});

test('It fetches empty product attribute list', async () => {
  const response = [];

  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => response,
  }));

  const {result, waitForNextUpdate} = renderHookWithProviders(() => useProductAttributes('atmosphere'));

  await act(async () => {
    await waitForNextUpdate();
  });

  const [attributes, selectedAttribute] = result.current;
  expect(attributes).toEqual([]);
  expect(selectedAttribute).toEqual(null);
});
