import 'jest-fetch-mock';
import React from 'react';
import {
  renderWithProviders,
  screen,
  act,
  waitForElementToBeRemoved,
} from '../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import { MeasurementAttributeCondition } from '../../../../../../src/models/conditions';
import { Operator } from '../../../../../../src/models/Operator';
import {
  createAttribute,
  locales,
  measurementFamiliesResponse,
  scopes,
} from '../../../../factories';
import { MeasurementAttributeConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/MeasurementAttributeConditionLine';
import { clearAttributeRepositoryCache } from '../../../../../../src/repositories/AttributeRepository';

describe('MeasurementAttributeConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  const attribute = createAttribute({
    code: 'weight',
    type: 'pim_catalog_metric',
    metric_family: 'weight_metric_family',
    default_metric_unit: 'KILOGRAM',
    decimals_allowed: true,
    labels: {
      en_US: 'Weight',
    },
  });

  it('should display a measurement attribute condition line with scope and locale selector', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(JSON.stringify(attribute));
      }

      if (request.url.includes('pim_enrich_measures_rest_index')) {
        return Promise.resolve(JSON.stringify(measurementFamiliesResponse));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: MeasurementAttributeCondition = {
      field: 'weight',
      operator: Operator.EQUALS,
    };

    const toRegister = [
      { name: 'content.conditions[0].value', type: 'custom' },
      { name: 'content.conditions[0].operator', type: 'custom' },
      { name: 'content.conditions[0].locale', type: 'custom' },
      { name: 'content.conditions[0].scope', type: 'custom' },
    ];
    const defaultValues = {
      content: {
        conditions: [
          {
            field: 'weight',
            operator: Operator.EQUALS,
            value: {
              unit: 'KILOGRAM',
              amount: 100,
            },
            scope: 'ecommerce',
            locale: 'en_US',
          },
        ],
      },
    };

    renderWithProviders(
      <MeasurementAttributeConditionLine
        lineNumber={0}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('Weight')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-0-operator');
    expect(inputOperator).toHaveValue(Operator.EQUALS);

    const amountInput = screen.queryByTestId('edit-rules-input-0-value-amount');
    expect(amountInput).toBeInTheDocument();
    expect(amountInput).toHaveValue(100);

    const unitInput = screen.queryByTestId('edit-rules-input-0-value-unit');
    expect(unitInput).toBeInTheDocument();
    expect(unitInput).toHaveValue('KILOGRAM');

    const scopeInput = screen.queryByTestId('edit-rules-input-0-scope');
    expect(scopeInput).toBeInTheDocument();
    expect(scopeInput).toHaveValue('ecommerce');

    const localeInput = screen.queryByTestId('edit-rules-input-0-locale');
    expect(localeInput).toBeInTheDocument();
    expect(localeInput).toHaveValue('en_US');
  });

  it('should display or hide the measurement input depending on the operator', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_attribute_rest_get')) {
        return Promise.resolve(
          JSON.stringify({
            ...attribute,
            scopable: false,
            localizable: false,
          })
        );
      }

      if (request.url.includes('pim_enrich_measures_rest_index')) {
        return Promise.resolve(JSON.stringify(measurementFamiliesResponse));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const condition: MeasurementAttributeCondition = {
      field: 'weight',
      operator: Operator.IS_EMPTY,
    };

    const toRegister = [
      { name: 'content.conditions[0].value', type: 'custom' },
      { name: 'content.conditions[0].operator', type: 'custom' },
      { name: 'content.conditions[0].locale', type: 'custom' },
      { name: 'content.conditions[0].scope', type: 'custom' },
    ];
    const defaultValues = {
      content: {
        conditions: [
          {
            field: 'weight',
            operator: Operator.IS_EMPTY,
          },
        ],
      },
    };

    renderWithProviders(
      <MeasurementAttributeConditionLine
        lineNumber={0}
        currentCatalogLocale={'en_US'}
        condition={condition}
        locales={locales}
        scopes={scopes}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('Weight')).toBeInTheDocument();

    const inputOperator = screen.getByTestId('edit-rules-input-0-operator');
    expect(inputOperator).toHaveValue(Operator.IS_EMPTY);

    expect(
      screen.queryByTestId('edit-rules-input-0-scope')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-input-0-locale')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-input-0-value-amount')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-input-0-value-unit')
    ).not.toBeInTheDocument();

    act(() => {
      userEvent.selectOptions(inputOperator, Operator.GREATER_THAN);
    });

    expect(screen.getByTestId('edit-rules-input-0-operator')).toHaveValue('>');

    await waitForElementToBeRemoved(() =>
      screen.queryByTestId('akeneo-spinner')
    ).then(() => {
      expect(
        screen.queryByTestId('edit-rules-input-0-value-amount')
      ).toBeInTheDocument();
      expect(
        screen.queryByTestId('edit-rules-input-0-value-unit')
      ).toBeInTheDocument();
    });
  });
});
