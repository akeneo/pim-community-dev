import React from 'react';
import 'jest-fetch-mock';
import userEvent from '@testing-library/user-event';
import {
  act,
  renderWithProviders,
  screen,
} from '../../../../../../../test-utils';
import { createAttribute, createScope } from '../../../../../factories';
import { AttributeType } from '../../../../../../../src/models';
import { IndexedCurrencies } from '../../../../../../../src/repositories/CurrencyRepository';
import { RemoveCurrencyFromPriceCollectionValue } from '../../../../../../../src/pages/EditRules/components/actions/attribute/RemoveCurrencyFromPriceCollectionValue';

const currencies: IndexedCurrencies = {
  EUR: { code: 'EUR' },
  JPY: { code: 'JPY' },
  USD: { code: 'USD' },
};

const createPriceAttribute = (scopable = false) =>
  createAttribute({
    code: 'price',
    type: AttributeType.PRICE_COLLECTION,
    scopable: scopable,
  });

describe('RemoveCurrencyFromPriceCollectionValue', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display a currency selector with all active currencies', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const onChange = jest.fn();

    renderWithProviders(
      <RemoveCurrencyFromPriceCollectionValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={[
          { amount: 0, currency: 'JPY' },
          { amount: 10, currency: 'USD' },
        ]}
        attribute={createPriceAttribute()}
        onChange={onChange}
      />,
      { all: true }
    );

    expect(
      await screen.findByText(
        'pim_enrich.entity.currency.plural_label pim_common.required_label'
      )
    ).toBeInTheDocument();
    const currencyInput = screen.getByTestId('attribute-value-id');
    expect(currencyInput?.children?.length).toBe(4); // USD, EUR, JPY and an empty option as placeholder
    expect(currencyInput).toHaveValue(['JPY', 'USD']);

    act(() => {
      userEvent.selectOptions(currencyInput, ['EUR']);
      expect(onChange).toHaveBeenLastCalledWith([
        { amount: 0, currency: 'EUR' },
        { amount: 0, currency: 'JPY' },
        { amount: 0, currency: 'USD' },
      ]);
    });
  });

  it('should display a field by currency with a scope and filter non bound currencies', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(
          JSON.stringify([
            createScope({
              code: 'ecommerce',
              currencies: ['EUR', 'USD'],
            }),
          ])
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const onChange = jest.fn();

    renderWithProviders(
      <RemoveCurrencyFromPriceCollectionValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={[]}
        attribute={createPriceAttribute(true)}
        onChange={onChange}
        scopeCode={'ecommerce'}
      />,
      { all: true }
    );

    expect(
      await screen.findByText(
        'pim_enrich.entity.currency.plural_label pim_common.required_label'
      )
    ).toBeInTheDocument();

    const currencyInput = screen.getByTestId('attribute-value-id');
    expect(currencyInput?.children?.length).toBe(3); // USD, EUR and an empty option as placeholder
    expect(currencyInput).toHaveValue([]);

    act(() => {
      userEvent.selectOptions(currencyInput, ['EUR']);
    });

    expect(onChange).toHaveBeenLastCalledWith([{ amount: 0, currency: 'EUR' }]);
  });
});
