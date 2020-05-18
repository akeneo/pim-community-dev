import { render } from '../../../../test-utils';
import React from 'react';
import { TextAttributeConditionLine } from '../../../../src/pages/EditRules/components/conditions/TextAttributeConditionLine';
import { TextAttributeCondition } from '../../../../src/models/TextAttributeCondition';
import { Attribute } from '../../../../src/models/Attribute';
import { Operator } from '../../../../src/models/Operator';
import { IndexedScopes } from '../../../../src/repositories/ScopeRepository';
import { Router } from '../../../../src/dependenciesTools';
import userEvent from '@testing-library/user-event';
import { wait } from '@testing-library/dom';

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
    labels: {en_US: 'Name', fr_FR: 'Nom'},
    auto_option_sorting: null,
    is_read_only: false,
    empty_value: null,
    field_type: 'text',
    filter_types: {},
    is_locale_specific: false,
    meta: {},
    ...data
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
  attribute: createAttribute({localizable: false, scopable: false}),
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
  mobile: {
    code: 'mobile',
    currencies: ['EUR', 'USD'],
    locales: locales,
    category_tree: 'master',
    conversion_units: [],
    labels: { en_US: 'Mobile' },
    meta: {},
  },
};

const translate = jest.fn((key: string) => key);
const router: Router = {
  'generate': jest.fn(),
  'redirect': jest.fn(),
};

const formStateMock = {};
const setValueMock = jest.fn((key: string, value: any) => {
  formStateMock[key] = value;
});

jest.mock('react-hook-form', () => {
  return {
    useFormContext: () => {
      return {
        register: jest.fn(),
        setValue: setValueMock,
        getValues: () => {
          return formStateMock;
        },
      };
    },
  };
});
jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');

const initFormWithCondition = (condition: TextAttributeCondition, lineNumber: number): void => {
  setValueMock(`content.conditions[${lineNumber}].field`, condition.field);
  setValueMock(`content.conditions[${lineNumber}].operator`, condition.operator);
  setValueMock(`content.conditions[${lineNumber}].value`, condition.value);
  if (condition.scope) {
    setValueMock(`content.conditions[${lineNumber}].scope`, condition.scope);
  }
  if (condition.locale) {
    setValueMock(`content.conditions[${lineNumber}].locale`, condition.locale);
  }
};

describe('TextAttributeConditionLine', () => {
  it('should display the text attribute conditionWithLocalizableScopableAttribute with locale and scope selectors', async () => {
    initFormWithCondition(conditionWithLocalizableScopableAttribute, 1);
    const { findByText, findByTestId } = render(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
      />
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('!=');

    expect(await findByTestId('edit-rules-input-1-scope')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-locale')).toBeInTheDocument();
  });

  it('should display the text attribute conditionWithLocalizableScopableAttribute without locale and scope selectors', async () => {
    initFormWithCondition(conditionWithNonLocalizableScopableAttribute, 1);
    const { findByText, findByTestId, queryByTestId } = render(
      <TextAttributeConditionLine
        condition={conditionWithNonLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
      />
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();

    expect(queryByTestId('edit-rules-input-1-scope')).toBeNull();
    expect(queryByTestId('edit-rules-input-1-locale')).toBeNull();
  });

  it('handles values option appearance based on selected operator', async () => {
    // Given
    initFormWithCondition(conditionWithLocalizableScopableAttribute, 1);
    const { findByText, findByTestId, queryByTestId } = render(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        router={router}
      />
    );
    expect(await findByText('Name')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    // When
    userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
    // Then
    expect(setValueMock).toHaveBeenCalledWith('content.conditions[1].operator', Operator.IS_NOT_EMPTY);
    expect(setValueMock).toHaveBeenCalledWith('content.conditions[1].value', null);
    await wait(() => expect(queryByTestId('edit-rules-input-1-value')).toBeNull());
    // When
    userEvent.selectOptions(operatorSelector, Operator.NOT_EQUAL);
    // Then
    expect(setValueMock).toHaveBeenCalledWith('content.conditions[1].operator', Operator.NOT_EQUAL);
    expect(setValueMock).toHaveBeenCalledWith('content.conditions[1].value', 'Canon');
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();
  });

  it('handles locale and channel changes', async () => {
    // Given
    initFormWithCondition(conditionWithLocalizableScopableAttribute, 1);
    const { findByTestId } = render(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
      />
    );
    const scopeSelector = await findByTestId('edit-rules-input-1-scope');
    expect(scopeSelector).toBeInTheDocument();
    expect(scopeSelector).toHaveValue('mobile');
    const localeSelector = await findByTestId('edit-rules-input-1-locale');
    expect(localeSelector).toBeInTheDocument();
    expect(localeSelector).toHaveValue('en_US');

    // When
    userEvent.selectOptions(scopeSelector, 'ecommerce');
    // Then
    expect(setValueMock).toHaveBeenCalledWith('content.conditions[1].scope', 'ecommerce');
    // When
    userEvent.selectOptions(localeSelector, 'fr_FR');
    // Then
    expect(setValueMock).toHaveBeenCalledWith('content.conditions[1].locale', 'fr_FR');
  });
});
