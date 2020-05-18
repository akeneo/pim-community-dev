import React from 'react';
import { render } from '../../../../test-utils';
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


const formStateMock = {};
const setValueMock = jest.fn((key: string, value: any) => {
  formStateMock[key] = value;
});

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('react-hook-form', () => {
  return {
    useFormContext: () => {
      return {
        register: jest.fn(),
        setValue: setValueMock,
        getValues: () => {
          return formStateMock;
        },
      };
    },
  };
});

describe('FamilyConditionLine', () => {
  it('should display the family condition line', async () => {
    const { findByText, findByTestId } = render(
      <FamilyConditionLine
        condition={condition}
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        router={router}
        locales={[]}
        scopes={{}}
        translate={translate}
      />
    );

    expect(await findByText('pimee_catalog_rule.form.edit.fields.family')).toBeInTheDocument();

    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-value')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.EMPTY')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.NOT EMPTY')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.IN')).toBeInTheDocument();
    expect(await findByText('Pimee_catalog_rule.form.edit.conditions.operators.NOT IN')).toBeInTheDocument();
  });

  it('handles values option appearance based on selected operator', async () => {
    const { findByTestId, queryByTestId } = render(
      <FamilyConditionLine
        condition={condition}
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        router={router}
        locales={[]}
        scopes={{}}
        translate={translate}
      />
    );

    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
    expect(setValueMock).toHaveBeenCalledTimes(2);
    expect(setValueMock).toHaveBeenNthCalledWith(1, 'content.conditions[1].operator', Operator.IS_NOT_EMPTY);
    expect(setValueMock).toHaveBeenNthCalledWith(2, 'content.conditions[1].value', null);
    expect(queryByTestId('edit-rules-input-1-value')).toBeNull();

    userEvent.selectOptions(operatorSelector, Operator.NOT_IN_LIST);
    expect(setValueMock).toHaveBeenCalledTimes(4);
    expect(setValueMock).toHaveBeenNthCalledWith(3, 'content.conditions[1].operator', Operator.NOT_IN_LIST);
    expect(setValueMock).toHaveBeenNthCalledWith(4, 'content.conditions[1].value', []);
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();
  });
});
