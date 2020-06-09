import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders, act } from '../../../../test-utils';
import { Operator } from '../../../../src/models/Operator';
import { FamilyCondition } from '../../../../src/models/conditions';
import { FamilyConditionLine } from '../../../../src/pages/EditRules/components/conditions/FamilyConditionLine';
import userEvent from '@testing-library/user-event';

jest.mock('../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');

const condition: FamilyCondition = {
  field: 'family',
  operator: Operator.IN_LIST,
  value: ['accessories', 'mugs'],
};

const familiesPayload = {
  accessories: {
    code: 'accessories',
    labels: { en_US: 'Accessories', fr_FR: 'Accessoires' },
  },
  mugs: { code: 'mugs', labels: { en_US: 'Mugs', fr_FR: 'Tasses' } },
};

describe('FamilyConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the family condition line', async () => {
    fetchMock.mockResponses([JSON.stringify(familiesPayload), { status: 200 }]);

    const {
      findByText,
      findByTestId,
    } = renderWithProviders(
      <FamilyConditionLine
        condition={condition}
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      { all: true }
    );

    expect(
      await findByText('pimee_catalog_rule.form.edit.fields.family')
    ).toBeInTheDocument();
    expect(
      await findByTestId('edit-rules-input-1-operator')
    ).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-operator')).toHaveValue(
      Operator.IN_LIST
    );
    expect(await findByTestId('edit-rules-input-1-value')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-value')).toHaveValue([
      'accessories',
      'mugs',
    ]);
    expect(
      await findByText(
        'Pimee_catalog_rule.form.edit.conditions.operators.EMPTY'
      )
    ).toBeInTheDocument();
    expect(
      await findByText(
        'Pimee_catalog_rule.form.edit.conditions.operators.NOT EMPTY'
      )
    ).toBeInTheDocument();
    expect(
      await findByText('Pimee_catalog_rule.form.edit.conditions.operators.IN')
    ).toBeInTheDocument();
    expect(
      await findByText(
        'Pimee_catalog_rule.form.edit.conditions.operators.NOT IN'
      )
    ).toBeInTheDocument();
  });

  it('handles values option appearance based on selected operator', async () => {
    fetchMock.mockResponses([JSON.stringify(familiesPayload), { status: 200 }]);

    const {
      findByTestId,
      queryByTestId,
    } = renderWithProviders(
      <FamilyConditionLine
        condition={condition}
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      { all: true }
    );

    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    act(() => userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY));
    expect(queryByTestId('edit-rules-input-1-value')).toBeNull();
    act(() => userEvent.selectOptions(operatorSelector, Operator.NOT_IN_LIST));
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();
  });
});
