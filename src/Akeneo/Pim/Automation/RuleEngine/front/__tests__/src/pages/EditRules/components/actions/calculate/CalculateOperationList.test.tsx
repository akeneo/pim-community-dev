import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  renderWithProviders,
  screen,
  fireEvent,
} from '../../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import { clearAttributeRepositoryCache } from '../../../../../../../src/repositories/AttributeRepository';
import {
  createAttribute,
  currencies,
  locales,
  scopes,
} from '../../../../../factories';
import { CalculateOperationList } from '../../../../../../../src/pages/EditRules/components/actions/calculate/CalculateOperationList';
import { AttributeType } from '../../../../../../../src/models';

jest.mock('../../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../../src/dependenciesTools/provider/dependencies.ts'
);
jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

const defaultValues = {
  content: {
    actions: [
      {
        type: 'calculate',
        destination: {
          field: 'weight',
        },
        round_precision: 0,
        full_operation_list: [
          {
            field: 'margin',
          },
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
      },
    ],
  },
};

const marginAttribute = createAttribute({
  type: AttributeType.NUMBER,
  labels: {
    en_US: 'Margin',
    fr_FR: 'Marge',
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

describe('CalculateOperationList', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the operation list', async () => {
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
        lineNumber={0}
        scopes={scopes}
        locales={locales}
      />,
      { all: true },
      { defaultValues }
    );

    expect(await screen.findByText('PriceUS')).toBeInTheDocument();
    expect(await screen.findByText('Margin')).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-number')
    ).toHaveValue(12);
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-currency')
    ).toHaveValue('USD');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-scope')
    ).toHaveValue('mobile');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-locale')
    ).toHaveValue('en_US');
  });

  it('should be able to remove an operation', async () => {
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
        lineNumber={0}
        scopes={scopes}
        locales={locales}
      />,
      { all: true },
      { defaultValues }
    );

    expect(await screen.findByText('Margin')).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-number')
    ).toHaveValue(12);
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-currency')
    ).toHaveValue('USD');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-scope')
    ).toHaveValue('mobile');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-locale')
    ).toHaveValue('en_US');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-0-remove-button')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-3-remove-button')
    ).toBeInTheDocument();

    await act(async () => {
      userEvent.click(
        await screen.findByTestId(
          'edit-rules-action-operation-list-0-remove-button'
        )
      );
      expect(screen.queryByText('Margin')).not.toBeInTheDocument();
      expect(
        screen.getByTestId('edit-rules-action-operation-list-0-number')
      ).toHaveValue(12);
      expect(
        screen.getByTestId('edit-rules-action-operation-list-1-currency')
      ).toHaveValue('USD');
      expect(
        screen.getByTestId('edit-rules-action-operation-list-1-scope')
      ).toHaveValue('mobile');
      expect(
        screen.getByTestId('edit-rules-action-operation-list-1-locale')
      ).toHaveValue('en_US');
      expect(
        screen.queryByTestId('edit-rules-action-operation-list-3-remove-button')
      ).not.toBeInTheDocument();
    });
  });

  it('should be able to drag and drop an operation', async () => {
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
        lineNumber={0}
        scopes={scopes}
        locales={locales}
      />,
      { all: true },
      { defaultValues }
    );

    expect(await screen.findByText('Margin')).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-number')
    ).toHaveValue(12);
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-currency')
    ).toHaveValue('USD');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-scope')
    ).toHaveValue('mobile');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-2-locale')
    ).toHaveValue('en_US');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-0-remove-button')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-3-remove-button')
    ).toBeInTheDocument();

    const fromOperation = screen.getByTestId(
      'edit-rules-action-operation-list-0-item'
    );
    const toOperation = screen.getByTestId(
      'edit-rules-action-operation-list-2-item'
    );

    // The 2 events must be in 2 different act()
    act(() => {
      fireEvent.dragOver(toOperation);
    });
    act(() => {
      fireEvent.dragEnd(fromOperation);
    });

    expect(await screen.findByText('Margin')).toBeInTheDocument();
    expect(
      await screen.findByTestId('edit-rules-action-operation-list-0-number')
    ).toHaveValue(12);
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-currency')
    ).toHaveValue('USD');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-scope')
    ).toHaveValue('mobile');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-1-locale')
    ).toHaveValue('en_US');
    expect(
      screen.getByTestId('edit-rules-action-operation-list-3-remove-button')
    ).toBeInTheDocument();
  });
});
