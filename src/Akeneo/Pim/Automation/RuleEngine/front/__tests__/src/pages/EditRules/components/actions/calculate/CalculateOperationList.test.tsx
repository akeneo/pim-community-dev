import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders, screen } from '../../../../../../../test-utils';
import { clearAttributeRepositoryCache } from '../../../../../../../src/repositories/AttributeRepository';
import {
  createAttribute,
  currencies,
  locales,
  scopes,
} from '../../../../../factories';
import { CalculateOperationList } from '../../../../../../../src/pages/EditRules/components/actions/calculate/CalculateOperationList';
import { Operation } from '../../../../../../../src/models/actions/Calculate/Operation';
import { AttributeType } from '../../../../../../../src/models';

jest.mock('../../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../../src/dependenciesTools/provider/dependencies.ts'
);
jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

describe('CalculateOperationList', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the operation list', async () => {
    const action = {
      type: 'calculate',
      destination: {
        field: 'weight',
      },
      round_precision: 0,
      source: {
        field: 'margin',
      },
      operation_list: [
        {
          operator: 'add',
          value: 12,
        },
        {
          operator: 'multiply',
          field: 'price',
          locale: 'en_US',
          scope: 'mobile',
          currency: 'USD',
        },
        {
          operator: 'subtract',
          field: 'unknown_attribute',
        },
      ],
    };
    const marginAttribute = createAttribute({
      type: AttributeType.NUMBER,
      labels: {
        en_US: 'Margin',
        fr_FR: 'Marge',
        localizable: false,
        scopable: false,
      },
    });
    const priceAttribute = createAttribute({
      type: AttributeType.PRICE_COLLECTION,
      labels: {
        en_US: 'PriceUS',
        fr_FR: 'Prix',
        localizable: true,
        scopable: true,
      },
    });
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes('pim_enrich_attribute_rest_get') &&
        request.url.includes('margin')
      ) {
        return Promise.resolve(JSON.stringify(marginAttribute));
      }
      if (
        request.url.includes('pim_enrich_attribute_rest_get') &&
        request.url.includes('price')
      ) {
        return Promise.resolve(JSON.stringify(priceAttribute));
      }
      if (
        request.url.includes('pim_enrich_attribute_rest_get') &&
        request.url.includes('unknown_attribute')
      ) {
        return Promise.resolve(JSON.stringify(null));
      }
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <CalculateOperationList
        defaultSource={action.source}
        defaultOperationList={action.operation_list as Operation[]}
        scopes={scopes}
        locales={locales}
      />,
      { all: true }
    );

    expect(await screen.findByText('PriceUS')).toBeInTheDocument();
    expect(await screen.findByText('Margin')).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-number')
    ).toHaveValue(12);
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-price')
    ).toHaveValue('USD');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-scope')
    ).toHaveValue('mobile');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-locale')
    ).toHaveValue('en_US');
  });
});
