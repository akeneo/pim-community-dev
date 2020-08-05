import 'jest-fetch-mock';
import React from 'react';
import { render, screen } from '../../../../../../test-utils';
import { CategoryConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/CategoryConditionLine';
import { Operator } from '../../../../../../src/models/Operator';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector');
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('CategoryConditionLine', () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });
  it('should display the category line with existing categories', async () => {
    const categoriesPayload = [
      {
        code: 'shoes',
        parent: 'master',
        labels: { fr_FR: 'Chaussures' },
        id: 42,
      },
      {
        code: 'tshirts',
        parent: 'master',
        labels: { en_US: 'Tshirts' },
        id: 43,
      },
    ];
    fetchMock.mockResponses(
      [JSON.stringify(categoriesPayload), { status: 200 }],
      [JSON.stringify(categoriesPayload), { status: 200 }],
      [JSON.stringify(categoriesPayload), { status: 200 }]
    );
    const defaultValues = {
      content: {
        conditions: [
          {},
          {},
          {
            field: 'categories',
            operator: Operator.NOT_IN_CHILDREN_LIST,
            value: ['shoes', 'tshirts'],
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[2].field', type: 'custom' },
      { name: 'content.conditions[2].value', type: 'custom' },
      { name: 'content.conditions[2].operator', type: 'custom' },
    ];

    render(
      <CategoryConditionLine
        lineNumber={2}
        locales={[]}
        scopes={{}}
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(
      screen.getByText('pimee_catalog_rule.form.edit.fields.category')
    ).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-2-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('NOT IN CHILDREN');
    expect(await screen.findByText('Chaussures')).toBeInTheDocument();
    expect(await screen.findByText('[tshirts]')).toBeInTheDocument();
  });
});
