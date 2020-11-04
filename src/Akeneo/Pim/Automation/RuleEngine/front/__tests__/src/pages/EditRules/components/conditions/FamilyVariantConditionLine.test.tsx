import React from 'react';
import 'jest-fetch-mock';
import {act, renderWithProviders, screen} from '../../../../../../test-utils';
import {Operator} from '../../../../../../src/models/Operator';
import userEvent from '@testing-library/user-event';
import {FamilyVariantConditionLine} from '../../../../../../src/pages/EditRules/components/conditions/FamilyVariantConditionLine';

const familyVariantsPayload = {
  clothing_by_size: {
    code: 'clothing_by_size',
    labels: {en_US: 'Clothing by size', fr_FR: 'Vêtements par taille'},
  },
  clothing_by_color: {
    code: 'clothing_by_color',
    labels: {en_US: 'Clothing by color', fr_FR: 'Vêtements par couleur'},
  },
};

describe('FamilyVariantConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display a family variant condition line', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (request.url.includes('pim_enrich_family_variant_rest_index')) {
        return Promise.resolve(JSON.stringify(familyVariantsPayload));
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'family_variant',
            operator: Operator.NOT_IN_LIST,
            value: ['clothing_by_size', 'clothing_by_color'],
          },
        ],
      },
    };

    const toRegister = [
      {name: 'content.conditions[1].field', type: 'custom'},
      {name: 'content.conditions[1].value', type: 'custom'},
      {name: 'content.conditions[1].operator', type: 'custom'},
    ];
    renderWithProviders(
      <FamilyVariantConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.fields.family_variant'
      )
    ).toBeInTheDocument();

    const inputOperator = await screen.findByTestId(
      'edit-rules-input-1-operator'
    );
    expect(inputOperator).toBeInTheDocument();
    expect(inputOperator).toHaveValue(Operator.NOT_IN_LIST);
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value')).toHaveValue([
      'clothing_by_color',
      'clothing_by_size',
    ]);

    act(() => userEvent.selectOptions(inputOperator, Operator.IS_NOT_EMPTY));
    expect(screen.queryByTestId('edit-rules-input-1-value')).toBeNull();
  });
});
