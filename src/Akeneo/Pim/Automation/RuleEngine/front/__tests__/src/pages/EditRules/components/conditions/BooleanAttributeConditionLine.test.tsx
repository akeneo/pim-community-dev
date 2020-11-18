import 'jest-fetch-mock';
import React from 'react';
import {renderWithProviders, screen} from '../../../../../../test-utils';
import {BooleanAttributeCondition} from '../../../../../../src/models/conditions';
import {Operator} from '../../../../../../src/models/Operator';
import {createAttribute, locales, scopes} from '../../../../factories';
import {clearAttributeRepositoryCache} from '../../../../../../src/repositories/AttributeRepository';
import {BooleanAttributeConditionLine} from '../../../../../../src/pages/EditRules/components/conditions/BooleanAttributeConditionLine';

describe('BooleanAttributeConditionLine', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    clearAttributeRepositoryCache();
  });
  it('should display a new boolean attribute condition line', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              code: 'auto_focus',
              localizable: false,
              scopable: false,
              type: 'pim_catalog_boolean',
              labels: {
                en_US: 'Auto focus',
              },
            })
          )
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: BooleanAttributeCondition = {
      field: 'auto_focus',
      operator: Operator.EQUALS,
      value: false,
    };

    renderWithProviders(
      <BooleanAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      {all: true}
    );

    expect(await screen.findByText('Auto focus')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.EQUALS);

    const inputValue = screen.getByTestId('edit-rules-input-1-value');
    expect(inputValue).not.toBeChecked();
  });

  it('should display an existing boolean attribute condition line', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              code: 'auto_focus',
              localizable: false,
              scopable: false,
              type: 'pim_catalog_boolean',
              labels: {
                en_US: 'Auto focus',
              },
            })
          )
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: BooleanAttributeCondition = {
      field: 'auto_focus',
      operator: Operator.EQUALS,
      value: false,
    };

    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'auto_focus',
            operator: Operator.NOT_EQUAL,
            value: true,
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[1].value', type: 'custom'},
      {name: 'content.conditions[1].operator', type: 'custom'},
      {name: 'content.conditions[1].value', type: 'custom'},
    ];

    renderWithProviders(
      <BooleanAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(await screen.findByText('Auto focus')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.NOT_EQUAL);

    const inputValue = screen.getByTestId('edit-rules-input-1-value');
    expect(inputValue).toBeChecked();
  });

  it('should not display a value when boolean operator is empty', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify(
            createAttribute({
              code: 'auto_focus',
              localizable: false,
              scopable: false,
              type: 'pim_catalog_boolean',
              labels: {
                en_US: 'Auto focus',
              },
            })
          )
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: BooleanAttributeCondition = {
      field: 'auto_focus',
      operator: Operator.IS_EMPTY
    };

    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'auto_focus',
            operator: Operator.IS_EMPTY
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[1].value', type: 'custom'},
      {name: 'content.conditions[1].operator', type: 'custom'},
      {name: 'content.conditions[1].value', type: 'custom'},
    ];

    renderWithProviders(
      <BooleanAttributeConditionLine
        lineNumber={1}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    expect(await screen.findByText('Auto focus')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-1-operator');
    expect(inputOperator).toHaveValue(Operator.IS_EMPTY);

    expect(await screen.queryByTestId('edit-rules-input-1-value')).not.toBeInTheDocument(); 
  });
});
