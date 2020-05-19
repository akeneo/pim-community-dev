import React from 'react';
import { renderWithProviders } from '../../../../test-utils';
import { Operator } from '../../../../src/models/Operator';
import { Router } from '../../../../src/dependenciesTools';
import { FamilyCondition } from '../../../../src/models/FamilyCondition';
import { FamilyConditionLine } from '../../../../src/pages/EditRules/components/conditions/FamilyConditionLine';
import userEvent from '@testing-library/user-event';

const condition: FamilyCondition = {
  module: FamilyConditionLine,
  field: 'family',
  operator: Operator.IN_LIST,
  value: ['accessories', 'mugs'],
  families: {
    'accessories': { 'code': 'accessories', 'labels': {'en_US': 'Accessories', 'fr_FR': 'Accessoires' } },
    'mugs': { 'code': 'mugs', 'labels': { 'en_US': 'Mugs', 'fr_FR': 'Tasses' } },
  },
};

const translate = jest.fn((key: string) => key);
const router: Router = {
  'generate': jest.fn(),
  'redirect': jest.fn(),
};

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');

describe('FamilyConditionLine', () => {
  it('should display the family condition line', async () => {
    const { findByText, findByTestId } = renderWithProviders(
      <FamilyConditionLine
        condition={condition}
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        router={router}
        locales={[]}
        scopes={{}}
        translate={translate}
      />, { reactHookForm: true }
    );

    expect(await findByText('pimee_catalog_rule.form.edit.fields.family')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-operator')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-operator')).toHaveValue(Operator.IN_LIST);
    expect(await findByTestId('edit-rules-input-1-value')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-value')).toHaveValue(['accessories', 'mugs']);
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.EMPTY')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.NOT EMPTY')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.IN')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.NOT IN')).toBeInTheDocument();
  });

  it('handles values option appearance based on selected operator', async () => {
    const { findByTestId, queryByTestId } = renderWithProviders(
      <FamilyConditionLine
        condition={condition}
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        router={router}
        locales={[]}
        scopes={{}}
        translate={translate}
      />, { reactHookForm: true }
    );

    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
    expect(queryByTestId('edit-rules-input-1-value')).toBeNull();
    userEvent.selectOptions(operatorSelector, Operator.NOT_IN_LIST);
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

  });
});
