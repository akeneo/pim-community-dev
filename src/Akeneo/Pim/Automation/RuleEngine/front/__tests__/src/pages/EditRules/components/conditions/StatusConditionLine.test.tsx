import React from 'react';
import { renderWithProviders, screen } from '../../../../../../test-utils';
import { Operator } from '../../../../../../src/models/Operator';
import { StatusConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/StatusConditionLine';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector'
);
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('StatusConditionLine', () => {
  it('should display the status condition line', () => {
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'enabled',
            operator: Operator.NOT_EQUAL,
            value: false,
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
    ];

    renderWithProviders(
      <StatusConditionLine
        lineNumber={1}
        locales={[]}
        scopes={{}}
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(screen.getByText('pim_common.status')).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('!=');
    const statusSelector = screen.getByTestId('edit-rules-input-1-value');
    expect(statusSelector).toBeInTheDocument();
    expect(statusSelector).toHaveValue('disabled');
  });
});
