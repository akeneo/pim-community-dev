import { renderWithProviders } from '../../../../test-utils';
import React from 'react';
import { TextAttributeConditionLine } from '../../../../src/pages/EditRules/components/conditions/TextAttributeConditionLine';
import { TextAttributeCondition } from '../../../../src/models/conditions';
import { Attribute } from '../../../../src/models/Attribute';
import { Operator } from '../../../../src/models/Operator';
import { IndexedScopes } from '../../../../src/repositories/ScopeRepository';
import { Router } from '../../../../src/dependenciesTools';
import userEvent from '@testing-library/user-event';
import { wait } from '@testing-library/dom';

jest.mock('../../../../src/fetch/categoryTree.fetcher');

const createAttribute = (data: { [key: string]: any }): Attribute => {
  return {
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
    meta: {
      id: 42,
    },
    ...data,
  };
};

const conditionWithLocalizableScopableAttribute: TextAttributeCondition = {
  scope: 'mobile',
  module: TextAttributeConditionLine,
  locale: 'en_US',
  attribute: createAttribute({}),
  field: 'name',
  operator: Operator.NOT_EQUAL,
  value: 'Canon',
};

const conditionWithNonLocalizableScopableAttribute: TextAttributeCondition = {
  module: TextAttributeConditionLine,
  attribute: createAttribute({ localizable: false, scopable: false }),
  field: 'name',
  operator: Operator.NOT_EQUAL,
  value: 'Canon',
};

const defaultCondition: TextAttributeCondition = {
  module: TextAttributeConditionLine,
  attribute: createAttribute({ localizable: true, scopable: true }),
  field: 'name',
  operator: Operator.IS_EMPTY,
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
  mobile: {
    code: 'mobile',
    currencies: ['EUR', 'USD'],
    locales: [locales[0], locales[1]],
    category_tree: 'master',
    conversion_units: [],
    labels: { en_US: 'Mobile' },
    meta: {},
  },
};

const translate = jest.fn((key: string) => key);
const router: Router = {
  generate: jest.fn(),
  redirect: jest.fn(),
};

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');

describe('TextAttributeConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the text attribute conditionWithLocalizableScopableAttribute with locale and scope selectors', async () => {
    const {
      findByText,
      findByTestId,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
      />,
      { all: true }
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('!=');
    expect(await findByTestId('edit-rules-input-1-scope')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-scope')).toHaveValue(
      'mobile'
    );
    expect(await findByTestId('edit-rules-input-1-locale')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-locale')).toHaveValue(
      'en_US'
    );
  });

  it('should display the text attribute conditionWithLocalizableScopableAttribute without locale and scope selectors', async () => {
    const {
      findByText,
      findByTestId,
      queryByTestId,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={conditionWithNonLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
      />,
      { all: true }
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();

    expect(queryByTestId('edit-rules-input-1-scope')).toBeNull();
    expect(queryByTestId('edit-rules-input-1-locale')).toBeNull();
  });

  it('handles values option appearance based on selected operator', async () => {
    // Given
    const {
      findByText,
      findByTestId,
      queryByTestId,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        router={router}
      />,
      { all: true }
    );
    expect(await findByText('Name')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
    await wait(() =>
      expect(queryByTestId('edit-rules-input-1-value')).toBeNull()
    );

    userEvent.selectOptions(operatorSelector, Operator.NOT_EQUAL);
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();
  });

  it('displays the matching locales regarding the scope', async () => {
    // Given
    const {
      findByText,
      findByTestId,
      queryByTestId,
      queryByText,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={defaultCondition}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        router={router}
      />,
      { all: true }
    );
    expect(await findByText('Name')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(
      await findByTestId('edit-rules-input-1-scope'),
      'ecommerce'
    );
    expect(queryByText('German')).toBeInTheDocument();
    expect(queryByText('French')).toBeInTheDocument();
    expect(queryByText('English')).toBeInTheDocument();
    userEvent.selectOptions(
      await findByTestId('edit-rules-input-1-scope'),
      'mobile'
    );
    expect(queryByText('German')).toBeInTheDocument();
    expect(queryByText('French')).not.toBeInTheDocument();
    expect(queryByText('English')).toBeInTheDocument();
  });
});
