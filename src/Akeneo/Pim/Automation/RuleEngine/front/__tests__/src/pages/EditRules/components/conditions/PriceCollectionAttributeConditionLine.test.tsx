import React from 'react';
import {
  act,
  renderWithProviders,
  screen,
  waitForElementToBeRemoved,
} from '../../../../../../test-utils';
import 'jest-fetch-mock';
import {
  createAttribute,
  currencies,
  locales,
  scopes,
} from '../../../../factories';
import { Operator } from '../../../../../../src/models/Operator';
import userEvent from '@testing-library/user-event';
import { AttributeType } from '../../../../../../src/models';
import { PriceCollectionAttributeConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/PriceCollectionAttributeConditionLine';

const toRegister: { name: string; type: string }[] = [];
[0, 1].forEach(lineNumber => {
  [
    'field',
    'value',
    'value.amount',
    'value.currency',
    'operator',
    'scope',
    'locale',
  ].forEach(fieldName => {
    toRegister.push({
      name: `content.conditions[${lineNumber}].${fieldName}`,
      type: 'custom',
    });
  });
});

const defaultValues = {
  content: {
    conditions: [
      {
        operator: Operator.GREATER_OR_EQUAL_THAN,
        field: 'price',
        value: {
          amount: 100,
          currency: 'USD',
        },
      },
      {
        operator: Operator.GREATER_THAN,
        scope: 'mobile',
        locale: 'en_US',
        field: 'localizable_scopable_price',
        value: {
          amount: 99.9,
          currency: 'USD',
        },
      },
    ],
  },
};

const response = (request: Request) => {
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('localizable_scopable_price')
  ) {
    return Promise.resolve(
      JSON.stringify(
        createAttribute({
          code: 'localizable_scopable_price',
          localizable: true,
          scopable: true,
          type: AttributeType.PRICE_COLLECTION,
          labels: { en_US: 'Localizable and scopable Price', fr_FR: 'Prix' },
        })
      )
    );
  }
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('price')
  ) {
    return Promise.resolve(
      JSON.stringify(
        createAttribute({
          code: 'price',
          localizable: false,
          scopable: false,
          type: AttributeType.PRICE_COLLECTION,
          labels: { en_US: 'Price', fr_FR: 'Prix' },
        })
      )
    );
  }
  if (request.url.includes('pim_enrich_currency_rest_index')) {
    return Promise.resolve(JSON.stringify(currencies));
  }
  throw new Error(`The "${request.url}" url is not mocked.`);
};

describe('PriceCollectionAttributeConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    fetchMock.mockResponse(response);
  });

  it('should display the price filter without locale and scope selectors', async () => {
    renderWithProviders(
      <PriceCollectionAttributeConditionLine
        condition={defaultValues.content.conditions[0]}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('div img[alt="pim_common.loading"]')
    ).then(() => {
      const operatorSelector = screen.getByTestId(
        'edit-rules-input-0-operator'
      );
      expect(screen.getByText('Price')).toBeInTheDocument();
      expect(operatorSelector).toHaveValue('>=');
      expect(
        screen.queryByTestId('edit-rules-input-0-locale')
      ).not.toBeInTheDocument();
      expect(
        screen.queryByTestId('edit-rules-input-0-scope')
      ).not.toBeInTheDocument();
      expect(screen.getByTestId('edit-rules-input-0-amount-value')).toHaveValue(
        100
      );
      expect(
        screen.getByTestId('edit-rules-input-0-currency-value')
      ).toHaveValue('USD');

      act(() => {
        userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
      });
      expect(
        screen.queryByTestId('edit-rules-input-0-amount-value')
      ).toBeNull();
      expect(
        screen.queryByTestId('edit-rules-input-0-currency-value')
      ).toBeNull();
      act(() => {
        userEvent.selectOptions(operatorSelector, Operator.NOT_EQUAL);
      });
      expect(
        screen.getByTestId('edit-rules-input-0-amount-value')
      ).toBeDefined();
      expect(
        screen.getByTestId('edit-rules-input-0-currency-value')
      ).toBeDefined();
    });
  });

  it('should display the price filter with locale and scope selectors', async () => {
    renderWithProviders(
      <PriceCollectionAttributeConditionLine
        condition={defaultValues.content.conditions[1]}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('div img[alt="pim_common.loading"]')
    ).then(() => {
      expect(
        screen.getByText('Localizable and scopable Price')
      ).toBeInTheDocument();
      expect(screen.getByTestId('edit-rules-input-1-operator')).toHaveValue(
        '>'
      );
      expect(screen.getByTestId('edit-rules-input-1-locale')).toHaveValue(
        'en_US'
      );
      expect(screen.getByTestId('edit-rules-input-1-scope')).toHaveValue(
        'mobile'
      );
      expect(screen.getByTestId('edit-rules-input-1-amount-value')).toHaveValue(
        99.9
      );
      expect(
        screen.getByTestId('edit-rules-input-1-currency-value')
      ).toHaveValue('USD');
    });
  });

  it('should change the currency options according to the selected scopes', async () => {
    renderWithProviders(
      <PriceCollectionAttributeConditionLine
        condition={defaultValues.content.conditions[1]}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('div img[alt="pim_common.loading"]')
    ).then(() => {
      const scopeSelector = screen.getByTestId('edit-rules-input-1-scope');
      expect(scopeSelector).toHaveValue('mobile');
      expect(screen.getByText('USD')).toBeInTheDocument();
      expect(
        screen.getByTestId('edit-rules-input-1-currency-value')
      ).toHaveValue('USD');

      userEvent.selectOptions(scopeSelector, 'print');
      expect(screen.queryByText('USD')).not.toBeInTheDocument();
    });
  });
});
