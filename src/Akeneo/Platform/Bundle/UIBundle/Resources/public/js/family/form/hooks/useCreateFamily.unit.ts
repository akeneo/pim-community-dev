import {useCreateFamily} from './useCreateFamily';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';

test('It creates a family', async () => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => ({
      code: 'my_family_code',
      attributes: [
        {
          code: 'sku',
          type: 'pim_catalog_identifier',
          group: 'erp',
          unique: true,
          useable_as_grid_filter: true,
          allowed_extensions: [],
          metric_family: null,
          default_metric_unit: null,
          reference_data_name: null,
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
          sort_order: 1,
          localizable: false,
          scopable: false,
          labels: {
            de_DE: 'SKU',
            en_US: 'SKU',
            fr_FR: 'SKU',
          },
          guidelines: [],
          auto_option_sorting: null,
          default_value: null,
          is_read_only: false,
          empty_value: null,
          field_type: 'akeneo-text-field',
          filter_types: {
            'product-export-builder': 'akeneo-attribute-identifier-filter',
          },
          is_locale_specific: false,
          is_main_identifier: true,
          meta: {
            id: 1,
          },
        },
      ],
      attribute_as_label: 'sku',
      attribute_as_image: null,
      attribute_requirements: {
        ecommerce: ['sku'],
        mobile: ['sku'],
        print: ['sku'],
      },
      labels: [],
    }),
  }));

  const {result} = renderHookWithProviders(() => useCreateFamily());
  const createFamily = result.current;

  const response = await createFamily('my_family_code');

  expect(response).toEqual({
    code: 'my_family_code',
    attributes: [
      {
        code: 'sku',
        type: 'pim_catalog_identifier',
        group: 'erp',
        unique: true,
        useable_as_grid_filter: true,
        allowed_extensions: [],
        metric_family: null,
        default_metric_unit: null,
        reference_data_name: null,
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
        sort_order: 1,
        localizable: false,
        scopable: false,
        labels: {
          de_DE: 'SKU',
          en_US: 'SKU',
          fr_FR: 'SKU',
        },
        guidelines: [],
        auto_option_sorting: null,
        default_value: null,
        is_read_only: false,
        empty_value: null,
        field_type: 'akeneo-text-field',
        filter_types: {
          'product-export-builder': 'akeneo-attribute-identifier-filter',
        },
        is_locale_specific: false,
        is_main_identifier: true,
        meta: {
          id: 1,
        },
      },
    ],
    attribute_as_label: 'sku',
    attribute_as_image: null,
    attribute_requirements: {
      ecommerce: ['sku'],
      mobile: ['sku'],
      print: ['sku'],
    },
    labels: [],
  });
});
