import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders, screen } from '../../../../../../test-utils';
import { Operator } from '../../../../../../src/models/Operator';
import { CompletenessConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/CompletenessConditionLine';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock(
  '../../../../../../src/dependenciesTools/components/AssetManager/AssetSelector'
);
jest.mock(
  '../../../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('CompletenessConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the completeness condition line', async () => {
    const defaultValues = {
      content: {
        conditions: [
          {
            operator: Operator.EQUALS,
            value: 50,
            scope: 'ecommerce',
            locale: 'en_US',
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
      { name: 'content.conditions[1].locale', type: 'custom' },
      { name: 'content.conditions[1].scope', type: 'custom' },
    ];
    renderWithProviders(
      <CompletenessConditionLine
        lineNumber={0}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(
      await screen.findByText('pim_common.completeness')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-0-operator')
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-0-operator')).toHaveValue(
      Operator.EQUALS
    );
    expect(screen.getByTestId('edit-rules-input-0-value')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-0-value')).toHaveValue(50);

    expect(screen.getByTestId('edit-rules-input-0-scope')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-0-scope')).toHaveValue(
      'ecommerce'
    );

    expect(screen.getByTestId('edit-rules-input-0-locale')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-0-locale')).toHaveValue(
      'en_US'
    );
  });
});
