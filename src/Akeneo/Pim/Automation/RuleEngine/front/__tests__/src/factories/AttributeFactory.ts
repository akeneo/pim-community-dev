import { Attribute, AttributeType } from '../../../src/models';

export const createAttribute = (data: { [key: string]: any }): Attribute => {
  return {
    code: 'name',
    type: AttributeType.TEXT,
    group: 'marketing',
    unique: false,
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
    localizable: true,
    scopable: true,
    labels: { en_US: 'Name', fr_FR: 'Nom' },
    auto_option_sorting: null,
    is_read_only: false,
    empty_value: null,
    field_type: 'text',
    filter_types: {},
    is_locale_specific: false,
    meta: {
      id: 42,
    },
    ...data,
  };
};

export const attributeSelect2Response = [
  {
    id: 'marketing',
    text: 'Marketing',
    children: [
      {
        id: 'name',
        text: 'Name',
      },
      {
        id: 'description',
        text: 'Description',
      },
      {
        id: 'brand',
        text: 'Brand',
      },
      {
        id: 'margin',
        text: 'Margin',
      },
      {
        id: 'weight',
        text: 'Weight',
      },
    ],
  },
];

export const attributeOptionsSelect2Response = {
  results: [
    {
      id: 'test1',
      text: 'Test 1',
    },
    {
      id: 'test2',
      text: 'Test 2',
    },
    {
      id: 'test3',
      text: 'Test 3',
    },
  ],
};
