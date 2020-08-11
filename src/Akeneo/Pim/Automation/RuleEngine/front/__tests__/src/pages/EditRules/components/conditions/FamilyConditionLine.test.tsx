import React from 'react';
import 'jest-fetch-mock';
import { renderWithProviders, act, screen } from '../../../../../../test-utils';
import { Operator } from '../../../../../../src/models/Operator';
import { FamilyConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/FamilyConditionLine';
import userEvent from '@testing-library/user-event';

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
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            operator: Operator.IN_LIST,
            value: ['accessories', 'mugs'],
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
      <FamilyConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.fields.family')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-1-operator')
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-operator')).toHaveValue(
      Operator.IN_LIST
    );
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value')).toHaveValue([
      'accessories',
      'mugs',
    ]);
    expect(
      screen.getByText(
        'Pimee_catalog_rule.form.edit.conditions.operators.EMPTY'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'Pimee_catalog_rule.form.edit.conditions.operators.NOT EMPTY'
      )
    ).toBeInTheDocument();
    expect(
      screen.getByText('Pimee_catalog_rule.form.edit.conditions.operators.IN')
    ).toBeInTheDocument();
    expect(
      screen.getByText(
        'Pimee_catalog_rule.form.edit.conditions.operators.NOT IN'
      )
    ).toBeInTheDocument();
  });

  it('handles values option appearance based on selected operator', async () => {
    renderWithProviders(
      <FamilyConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      { all: true }
    );

    const operatorSelector = await screen.findByTestId(
      'edit-rules-input-1-operator'
    );
    expect(operatorSelector).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeDefined();
    expect(screen.getByTestId('edit-rules-input-1-operator')).toHaveValue(
      Operator.IN_LIST
    );
    act(() => userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY));
    expect(screen.queryByTestId('edit-rules-input-1-value')).toBeNull();
    act(() => userEvent.selectOptions(operatorSelector, Operator.NOT_IN_LIST));
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeDefined();
  });
});
