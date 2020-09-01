import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders, screen } from '../../../../../../../test-utils';
import { clearAttributeRepositoryCache } from '../../../../../../../src/repositories/AttributeRepository';
import { CalculatePreview } from '../../../../../../../src/pages/EditRules/components/actions/calculate/CalculatePreview';
import { createAttribute } from '../../../../../factories';

jest.mock('../../../../../../../src/fetch/categoryTree.fetcher.ts');

const toRegister = [
  { name: 'content.actions[0].destination.field', type: 'custom' },
  { name: 'content.actions[0].destination.unit', type: 'custom' },
  { name: 'content.actions[0].destination.locale', type: 'custom' },
  { name: 'content.actions[0].destination.scope', type: 'custom' },
  {
    name: 'content.actions[0].round_precision',
    type: 'custom',
  },
  { name: 'content.actions[0].operation_list', type: 'custom' },
  { name: 'content.actions[0].source', type: 'custom' },
];

describe('CalculatePreview', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearAttributeRepositoryCache();
  });

  it('should display the preview of a calculate action', async () => {
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'calculate',
            destination: {
              field: 'weight',
              unit: 'KILOGRAM',
              locale: 'en_US',
              scope: 'mobile',
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
      labels: { en_US: 'Margin', fr_FR: 'Marge' },
    });
    const priceAttribute = createAttribute({
      labels: { en_US: 'PriceUS', fr_FR: 'Prix' },
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
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    renderWithProviders(
      <CalculatePreview lineNumber={0} />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.preview')
    ).toBeInTheDocument();
    const calculatePreview = await screen.findByTestId('calculate-preview');
    expect(calculatePreview.textContent).toBe(
      '((Margin + 12) x PriceUS) - unknown_attribute'
    );
  });
});
