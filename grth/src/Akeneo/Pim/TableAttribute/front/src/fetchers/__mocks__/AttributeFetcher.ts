import {Router, LocaleCode} from '@akeneo-pim-community/shared';
import {Attribute, AttributeCode, AttributeWithOptions} from '../../models';
import {AttributeFetcherIndexParams} from '../AttributeFetcher';

const attribute: Attribute = {
  code: 'nutrition',
  labels: {
    en_US: 'Nutrition',
  },
  allowed_extensions: [],
  auto_option_sorting: null,
  available_locales: [],
  date_max: null,
  date_min: null,
  decimals_allowed: null,
  default_metric_unit: null,
  empty_value: null,
  field_type: 'akeneo-table-field',
  filter_types: {},
  group: 'marketing',
  is_locale_specific: false,
  is_read_only: false,
  localizable: false,
  max_characters: null,
  max_file_size: null,
  meta: {
    id: 42,
  },
  metric_family: null,
  minimum_input_length: null,
  negative_allowed: null,
  number_max: null,
  number_min: null,
  reference_data_name: null,
  type: 'pim_catalog_table',
  scopable: false,
  sort_order: 0,
  unique: false,
  useable_as_grid_filter: false,
  validation_regexp: null,
  validation_rule: null,
  wysiwyg_enabled: null,
  table_configuration: [
    {data_type: 'select', code: 'ingredient', labels: {en_US: 'Ingredients'}, validations: {}},
    {data_type: 'number', code: 'quantity', labels: {en_US: 'Quantity'}, validations: {}},
    {data_type: 'boolean', code: 'is_allergenic', labels: {en_US: 'Is allergenic'}, validations: {}},
    {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}, validations: {}},
    {
      data_type: 'select',
      code: 'nutrition_score',
      labels: {en_US: 'Nutrition score'},
      validations: {},
      options: [
        {code: 'A', labels: {}},
        {code: 'B', labels: {}},
        {code: 'C', labels: {}},
      ],
    },
  ],
};

const getAttribute = (overrideAttributes: any) => {
  return {...attribute, ...overrideAttributes};
};

const fetchAttribute = async (_router: Router, attributeCode: AttributeCode): Promise<Attribute> => {
  if (attributeCode === 'nutrition') {
    return new Promise(resolve => resolve(attribute));
  }
  if (attributeCode === 'city') {
    const recordAttribute = getAttribute({
      table_configuration: [
        {
          data_type: 'reference_entity',
          code: 'city',
          labels: {en_US: 'City'},
          validations: {},
          reference_entity_identifier: 'city',
        },
        {data_type: 'number', code: 'quantity', labels: {en_US: 'Quantity'}, validations: {}},
        {data_type: 'boolean', code: 'is_allergenic', labels: {en_US: 'Is allergenic'}, validations: {}},
        {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}, validations: {}},
        {
          data_type: 'select',
          code: 'nutrition_score',
          labels: {en_US: 'Nutrition score'},
          validations: {},
          options: [{code: 'A'}, {code: 'B'}, {code: 'C'}],
        },
        {
          data_type: 'reference_entity',
          code: 'city_column',
          labels: {en_US: 'City column'},
          validations: {},
          reference_entity_identifier: 'city',
        },
      ],
    });
    return new Promise(resolve => resolve(recordAttribute));
  }

  throw new Error(`Non mocked attribute ${attributeCode}`);
};

const query = async (_router: Router, _params: AttributeFetcherIndexParams): Promise<Attribute[]> => {
  return new Promise(resolve =>
    resolve([
      attribute,
      getAttribute({
        code: 'packaging',
        labels: {
          en_US: 'Packaging',
        },
      }),
    ])
  );
};

const findAttributeWithOptions = async (
  _router: Router,
  _localeCode: LocaleCode,
  offset: number,
  limit: number
): Promise<AttributeWithOptions[]> => {
  const attributes: AttributeWithOptions[] = [
    {
      code: 'simple_select_1',
      label: 'Simple Select 1',
      options_count: 42,
    },
    {
      code: 'simple_select_2',
      label: 'Simple Select 2',
      options_count: 69,
    },
    {
      code: 'simple_select_3',
      label: 'Simple Select 3',
      options_count: 51,
    },
  ];

  const attributeWithOptions = attributes.slice(offset, offset + limit);

  return new Promise<AttributeWithOptions[]>(resolve => {
    return resolve(attributeWithOptions);
  });
};

const AttributeFetcher = {
  fetch: fetchAttribute,
  query: query,
  findAttributeWithOptions: findAttributeWithOptions,
};

export {AttributeFetcher};
