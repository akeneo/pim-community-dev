import { render } from '../../../../test-utils';
import React from 'react';
import { TextAttributeConditionLine } from '../../../../src/pages/EditRules/TextAttributeConditionLine';
import { TextAttributeCondition } from '../../../../src/models/TextAttributeCondition';
import { Attribute } from '../../../../src/models/Attribute';
import { Operator } from '../../../../src/models/Operator';
import { IndexedScopes } from '../../../../src/fetch/ScopeFetcher';
import { Router } from '../../../../src/dependenciesTools';

const nameAttribute: Attribute = {
  code: 'name',
  type: 'pim_catalog_text',
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
  meta: {},
};

const condition: TextAttributeCondition = {
  scope: 'ecommerce',
  module: TextAttributeConditionLine,
  locale: 'en_US',
  attribute: nameAttribute,
  field: 'name',
  operator: Operator.NOT_EQUAL,
  value: 'Canon',
};

const locales = [
  {
    code: 'de_DE',
    label: 'German (Germany)',
    region: 'Germany',
    language: 'German',
  },
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
];
const scopes: IndexedScopes = {
  ecommerce: {
    code: 'ecommerce',
    currencies: ['EUR', 'USD'],
    locales: locales,
    category_tree: 'master',
    conversion_units: [],
    labels: { en_US: 'e-commerce' },
    meta: {},
  },
};

const translate = jest.fn((key: string) => key);
const router: Router = {
  'generate': jest.fn(),
  'redirect': jest.fn(),
};

jest.mock('react-hook-form', () => {
  return {
    useFormContext: () => {
      return {
        register: jest.fn(),
      };
    },
  };
});

jest.mock('react-hook-form', () => {
  return {
    useFormContext: () => {
      return {
        register: jest.fn(),
      };
    },
  };
});

describe('TextAttributeConditionLine', () => {
  it('should display the text attribute condition', async () => {
    const { findByText, findByTestId } = render(
      <TextAttributeConditionLine
        condition={condition}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
      />,
      { legacy: true }
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    // expect(operatorSelector).toHaveValue('!='); // TODO once the register form will be mocked

    expect(await findByTestId('edit-rules-input-1-scope')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-locale')).toBeInTheDocument();
  });
});
