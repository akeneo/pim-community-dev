import 'jest-fetch-mock';
import React from 'react';
import {
  act,
  fireEvent,
  renderWithProviders,
  screen,
} from '../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import {IdentifierAttributeCondition} from '../../../../../../src/models/conditions';
import {Operator} from '../../../../../../src/models/Operator';
import {createAttribute, locales, scopes} from '../../../../factories';
import {clearAttributeRepositoryCache} from '../../../../../../src/repositories/AttributeRepository';
import {IdentifierAttributeConditionLine} from '../../../../../../src/pages/EditRules/components/conditions/IdentifierAttributeConditionLine';

describe('IdentifierAttributeConditionLine', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    clearAttributeRepositoryCache();
  });

  const sku = createAttribute({
    code: 'sku',
    localizable: false,
    scopable: false,
    type: 'pim_catalog_identifier',
    labels: {
      en_US: 'SKU',
    },
  });

  it('should display a new identifier attribute condition line', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(JSON.stringify(sku));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: IdentifierAttributeCondition = {
      field: 'sku',
      operator: Operator.STARTS_WITH,
      value: '',
    };

    renderWithProviders(
      <IdentifierAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      {all: true}
    );

    expect(await screen.findByText('SKU')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.STARTS_WITH);

    const inputValue = screen.getByTestId('edit-rules-input-1-value-text');
    expect(inputValue).toHaveValue('');
  });

  it('should display an existing identifier attribute condition line', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(JSON.stringify(sku));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: IdentifierAttributeCondition = {
      field: 'sku',
      operator: Operator.IN_LIST,
      value: ['123456', 'abcdef'],
    };

    const defaultValues = {
      content: {
        conditions: [{}, condition],
      },
    };

    const toRegister = [
      {name: 'content.conditions[1].value', type: 'custom'},
      {name: 'content.conditions[1].operator', type: 'custom'},
      {name: 'content.conditions[1].value', type: 'custom'},
    ];

    renderWithProviders(
      <IdentifierAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(await screen.findByText('SKU')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.IN_LIST);

    const inputValueSelector = screen.getByTestId(
      'edit-rules-input-1-value-selector'
    );
    expect(inputValueSelector).toHaveValue(['123456', 'abcdef']);
  });

  it('should switch between selector and text input depending on the operator', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(JSON.stringify(sku));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: IdentifierAttributeCondition = {
      field: 'sku',
      operator: Operator.IN_LIST,
      value: ['123456', 'abcdef'],
    };

    const defaultValues = {
      content: {
        conditions: [{}, condition],
      },
    };

    const toRegister = [
      {name: 'content.conditions[1].value', type: 'custom'},
      {name: 'content.conditions[1].operator', type: 'custom'},
      {name: 'content.conditions[1].value', type: 'custom'},
    ];

    renderWithProviders(
      <IdentifierAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(await screen.findByText('SKU')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.IN_LIST);

    const valueTextInput = () =>
      screen.queryByTestId('edit-rules-input-1-value-text');
    const valueSelector = () =>
      screen.queryByTestId('edit-rules-input-1-value-selector');

    expect(valueTextInput()).not.toBeInTheDocument();
    expect(valueSelector()).toBeInTheDocument();
    expect(valueSelector()).toHaveValue(['123456', 'abcdef']);

    act(() => userEvent.selectOptions(inputOperator, Operator.NOT_IN_LIST));
    // the operator is compatible, the value should not change
    expect(valueTextInput()).not.toBeInTheDocument();
    expect(valueSelector()).toBeInTheDocument();
    expect(valueSelector()).toHaveValue(['123456', 'abcdef']);

    act(() => userEvent.selectOptions(inputOperator, Operator.EQUALS));
    // the operator is not compatible, the vaiue should be reset and the text input should be displayed
    expect(valueSelector()).not.toBeInTheDocument();
    expect(valueTextInput()).toBeInTheDocument();
    expect(valueTextInput()).toHaveValue('');

    act(() => {
      fireEvent.change(screen.getByTestId('edit-rules-input-1-value-text'), {
        target: {value: 'toto'},
      });
      userEvent.selectOptions(inputOperator, Operator.CONTAINS);
    });
    // the operator is compatible, the value should not change
    expect(valueSelector()).not.toBeInTheDocument();
    expect(valueTextInput()).toBeInTheDocument();
    expect(valueTextInput()).toHaveValue('toto');
  });
});
