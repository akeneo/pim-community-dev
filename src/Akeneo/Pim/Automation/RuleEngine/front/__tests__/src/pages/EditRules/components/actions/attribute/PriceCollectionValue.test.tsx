import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  renderWithProviders,
  screen,
} from '../../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import { createAttribute, createScope } from '../../../../../factories';
import { AttributeType } from '../../../../../../../src/models';
import { PriceCollectionValue } from '../../../../../../../src/pages/EditRules/components/actions/attribute/PriceCollectionValue';
import { IndexedCurrencies } from '../../../../../../../src/repositories/CurrencyRepository';

jest.mock('../../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../../src/dependenciesTools/provider/dependencies.ts'
);
jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

const currencies: IndexedCurrencies = {
  EUR: { code: 'EUR' },
  USD: { code: 'USD' },
};

const scope = createScope({
  currencies: ['EUR'],
});

const priceAttribute = createAttribute({
  type: AttributeType.PRICE_COLLECTION,
});

describe('PriceCollectionValue', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display a field by currency with all currencies', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <PriceCollectionValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={[]}
        attribute={priceAttribute}
        onChange={jest.fn()}
      />,
      { all: true }
    );

    expect(await screen.findByText('Name')).toBeInTheDocument();
    expect(
      await screen.findByTestId('attribute-value-id-USD')
    ).toBeInTheDocument();
    expect(
      await screen.findByTestId('attribute-value-id-EUR')
    ).toBeInTheDocument();
  });

  it('should display a field by currency with a scope', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }
      if (request.url.includes('pim_enrich_channel_rest_index')) {
        return Promise.resolve(JSON.stringify([scope]));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <PriceCollectionValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={[]}
        attribute={priceAttribute}
        onChange={jest.fn()}
        scopeCode={'ecommerce'}
      />,
      { all: true }
    );

    expect(
      screen.queryByTestId('attribute-value-id-USD')
    ).not.toBeInTheDocument();
    expect(
      await screen.findByTestId('attribute-value-id-EUR')
    ).toBeInTheDocument();
  });

  it('should trigger changes when adding a value', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const onChange = jest.fn();
    renderWithProviders(
      <PriceCollectionValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={[{ amount: 69, currency: 'EUR' }]}
        attribute={priceAttribute}
        onChange={onChange}
      />,
      { all: true }
    );

    await act(async () => {
      userEvent.type(await screen.findByTestId('attribute-value-id-USD'), '42');
    });
    expect(onChange).toHaveBeenLastCalledWith([
      { amount: 69, currency: 'EUR' },
      { amount: 42, currency: 'USD' },
    ]);
  });

  it('should trigger changes when deleting a value', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const onChange = jest.fn();
    renderWithProviders(
      <PriceCollectionValue
        id={'attribute-value-id'}
        name={'attribute-value-name'}
        value={[{ amount: 69, currency: 'EUR' }]}
        attribute={priceAttribute}
        onChange={onChange}
      />,
      { all: true }
    );

    await act(async () => {
      userEvent.type(
        await screen.findByTestId('attribute-value-id-EUR'),
        '{backspace}{backspace}'
      );
    });
    expect(onChange).toHaveBeenLastCalledWith([]);
  });
});
