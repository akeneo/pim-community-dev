'use strict';

import fetcher from 'akeneoassetmanager/infrastructure/fetcher/product/attribute';
import * as fetch from 'akeneoassetmanager/tools/fetch';

jest.mock('pim/router', () => {});
jest.mock('pim/security-context', () => {}, {virtual: true});
jest.mock('routing');

describe('akeneoassetmanager/infrastructure/fetcher/product/attribute', () => {
  it('It lists the product attributes linked products', async () => {
    const spy = jest.spyOn(fetch, 'postJSON');
    spy.mockImplementation(() =>
      Promise.resolve([
        {
          code: 'asset_collection_attribute',
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
          labels: {},
          auto_option_sorting: null,
          is_read_only: null,
        },
        {
          code: 'asset_collection_attribute',
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
          labels: {},
          auto_option_sorting: null,
          is_read_only: null,
        },
        {
          code: 'asset_collection_attribute',
          type: 'pim_catalog_asset_collection',
          group: 'marketing',
          unique: false,
          useable_as_grid_filter: false,
          allowed_extensions: [],
          metric_family: null,
          default_metric_unit: null,
          reference_data_name: 'notice',
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
          labels: {},
          auto_option_sorting: null,
          is_read_only: null,
        },
      ])
    );

    const response = await fetcher.fetchLinkedAssetAttributes('atmosphere');

    expect(response).toEqual([
      {
        code: 'asset_collection_attribute',
        type: 'pim_catalog_asset_collection',
        useableAsGridFilter: false,
        referenceDataName: 'atmosphere',
        labelCollection: {},
      },
      {
        code: 'asset_collection_attribute',
        type: 'pim_catalog_asset_collection',
        useableAsGridFilter: false,
        referenceDataName: 'atmosphere',
        labelCollection: {},
      },
    ]);
  });
});
