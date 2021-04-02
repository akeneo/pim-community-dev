import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  renderWithProviders,
  screen,
  fireEvent,
} from '../../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import {clearAttributeRepositoryCache} from '../../../../../../../src/repositories/AttributeRepository';
import {
  attributeSelect2Response,
  createAttribute,
  currencies,
  locales,
  scopes,
  uiLocales,
} from '../../../../../factories';
import {CalculateOperationList} from '../../../../../../../src/pages/EditRules/components/actions/calculate/CalculateOperationList';
import {AttributeType} from '../../../../../../../src/models';

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

const descriptionAttribute = createAttribute({
  type: AttributeType.NUMBER,
  labels: {
    en_US: 'DescriptionUS',
    fr_FR: 'Description',
  },
  localizable: true,
  scopable: true,
});

const response = (request: Request) => {
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
    request.url.includes('description')
  ) {
    return Promise.resolve(JSON.stringify(descriptionAttribute));
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
  if (
    request.url.includes('pimee_enrich_rule_definition_get_available_fields')
  ) {
    return Promise.resolve(JSON.stringify(attributeSelect2Response));
  }
  throw new Error(`The "${request.url}" url is not mocked.`);
};

describe('CalculateOperationList', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the operation list', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateOperationList
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
      />,
      {all: true},
      {defaultValues}
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
    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateOperationList
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
      />,
      {all: true},
      {defaultValues}
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

  it('should be able to add a constant value', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateOperationList
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
      />,
      {all: true},
      {defaultValues}
    );

    expect(await screen.findByText('Margin')).toBeInTheDocument();

    userEvent.click(await screen.findByTestId('edit-rules-action-0-add-value'));
    expect(
      screen.getByTestId('edit-rules-action-operation-list-4-remove-button')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-4-number')
    ).toBeInTheDocument();
  });

  it('should be able to add a localizable and scopable attribute', async () => {
    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateOperationList
        lineNumber={0}
        scopes={scopes}
        locales={locales}
        uiLocales={uiLocales}
      />,
      {all: true},
      {defaultValues}
    );

    expect(await screen.findByText('Margin')).toBeInTheDocument();

    userEvent.click(
      await screen.findByTestId('edit-rules-action-0-add-attribute')
    );
    expect(
      (await screen.findByTestId('edit-rules-action-0-add-attribute')).children
        .length
    ).toBeGreaterThan(1);
    fireEvent.change(
      await screen.findByTestId('edit-rules-action-0-add-attribute'),
      {
        target: {value: 'description'},
      }
    );
    expect(await screen.findByText('DescriptionUS')).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-4-remove-button')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-4-locale')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-action-operation-list-4-scope')
    ).toBeInTheDocument();
  });
});
