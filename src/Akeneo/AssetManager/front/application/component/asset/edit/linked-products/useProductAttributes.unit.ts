import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useProductAttributes} from './useProductAttributes';

test('It fetches product attributes', async () => {
  const response = [
    {
      code: 'Test_asset',
      type: 'pim_catalog_asset_collection',
      group: 'marketing',
      unique: false,
      useable_as_grid_filter: false,
      allowed_extensions: [],
      metric_family: null,
      default_metric_unit: null,
      reference_data_name: 'atmosphere',
      available_locales: [],
      max_characters: null,
      validation_rule: null,
      validation_regexp: null,
      wysiwyg_enabled: null,
      number_min: null,
      number_max: null,
      decimals_allowed: null,
      negative_allowed: null,
      date_min: null,
      date_max: null,
      max_file_size: null,
      minimum_input_length: null,
      sort_order: 0,
      localizable: false,
      scopable: false,
      labels: {en_US: 'Test asset'},
      guidelines: [],
      auto_option_sorting: null,
      is_read_only: null,
      default_value: null,
      empty_value: [],
      field_type: 'pim-asset-collection-field',
      filter_types: {'product-export-builder': 'akeneo-attribute-assets-collection-filter'},
      is_locale_specific: false,
      meta: {id: 78},
    },
    {
      code: 'Yolo',
      type: 'pim_catalog_asset_collection',
      group: 'marketing',
      unique: false,
      useable_as_grid_filter: false,
      allowed_extensions: [],
      metric_family: null,
      default_metric_unit: null,
      reference_data_name: 'atmosphere',
      available_locales: [],
      max_characters: null,
      validation_rule: null,
      validation_regexp: null,
      wysiwyg_enabled: null,
      number_min: null,
      number_max: null,
      decimals_allowed: null,
      negative_allowed: null,
      date_min: null,
      date_max: null,
      max_file_size: null,
      minimum_input_length: null,
      sort_order: 0,
      localizable: false,
      scopable: false,
      labels: {en_US: 'Yolo'},
      guidelines: [],
      auto_option_sorting: null,
      is_read_only: null,
      default_value: null,
      empty_value: [],
      field_type: 'pim-asset-collection-field',
      filter_types: {'product-export-builder': 'akeneo-attribute-assets-collection-filter'},
      is_locale_specific: false,
      meta: {id: 79},
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
