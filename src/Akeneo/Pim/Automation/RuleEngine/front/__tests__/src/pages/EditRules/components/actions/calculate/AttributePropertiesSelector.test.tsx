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
import { AttributeType } from '../../../../../../../src/models';
import { AttributePropertiesSelector } from '../../../../../../../src/pages/EditRules/components/actions/calculate/AttributePropertiesSelector';

jest.mock('../../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../../src/dependenciesTools/provider/dependencies.ts'
);
jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

const marginAttribute = createAttribute({
  type: AttributeType.NUMBER,
  labels: {
    en_US: 'The Margin',
    fr_FR: 'La Marge',
  },
  localizable: false,
  scopable: false,
});
const priceAttribute = createAttribute({
  type: AttributeType.PRICE_COLLECTION,
  labels: {
    en_US: 'PriceUS',
    fr_FR: 'Prix',
  },
  localizable: true,
  scopable: true,
});

const toRegister = [
  { name: 'attribute.field.field', type: 'custom' },
  { name: 'attribute.field.locale', type: 'custom' },
  { name: 'attribute.field.scope', type: 'custom' },
  { name: 'attribute.field.currency', type: 'custom' },
];

describe('AttributePropertiesSelector', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the non localizable and non scopable margin attribute', async () => {
    const defaultValues = {
      attribute: {
        field: 'margin',
      },
    };
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes('pim_enrich_attribute_rest_get') &&
        request.url.includes('margin')
      ) {
        return Promise.resolve(JSON.stringify(marginAttribute));
      }
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <AttributePropertiesSelector
        operationLineNumber={0}
        attributeCode={defaultValues.attribute.field}
        fieldFormName={'attribute.field'}
        scopeFormName={'attribute.scope'}
        localeFormName={'attribute.locale'}
        currencyFormName={'attribute.currency'}
        scopes={scopes}
        locales={locales}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('The Margin')).toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-action-operation-list-0-currency')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-action-operation-list-0-scope')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-action-operation-list-0-locale')
    ).not.toBeInTheDocument();
  });

  it('should display the localizable and scopable price attribute', async () => {
    const defaultValues = {
      attribute: {
        field: 'price',
        currency: 'EUR',
        scope: 'mobile',
        locale: 'fr_FR',
      },
    };
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes('pim_enrich_attribute_rest_get') &&
        request.url.includes('price')
      ) {
        return Promise.resolve(JSON.stringify(priceAttribute));
      }
      if (request.url.includes('pim_enrich_currency_rest_index')) {
        return Promise.resolve(JSON.stringify(currencies));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <AttributePropertiesSelector
        operationLineNumber={0}
        attributeCode={defaultValues.attribute.field}
        fieldFormName={'attribute.field'}
        scopeFormName={'attribute.scope'}
        localeFormName={'attribute.locale'}
        currencyFormName={'attribute.currency'}
        scopes={scopes}
        locales={locales}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('PriceUS')).toBeInTheDocument();
    expect(
      await screen.findByTestId('edit-rules-action-operation-list-0-currency')
    ).toHaveValue('EUR');
    expect(
      await screen.findByTestId('edit-rules-action-operation-list-0-scope')
    ).toHaveValue('mobile');
    expect(
      await screen.findByTestId('edit-rules-action-operation-list-0-locale')
    ).toHaveValue('fr_FR');
  });
});
