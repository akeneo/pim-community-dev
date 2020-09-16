import React from 'react';
import 'jest-fetch-mock';
import { act, renderWithProviders, screen } from '../../../../../../test-utils';
import { Operator } from '../../../../../../src/models/Operator';
import userEvent from '@testing-library/user-event';
import { ParentConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/ParentConditionLine';

describe('ParentConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display a parent condition line', async () => {
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'parent',
            operator: Operator.IN_LIST,
            value: ['amor', 'bluesky'],
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
      <ParentConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.fields.parent')
    ).toBeInTheDocument();

    const inputOperator = await screen.findByTestId(
      'edit-rules-input-1-operator'
    );
    expect(inputOperator).toBeInTheDocument();
    expect(inputOperator).toHaveValue(Operator.IN_LIST);
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value')).toHaveValue([
      'amor',
      'bluesky',
    ]);

    act(() => userEvent.selectOptions(inputOperator, Operator.IS_NOT_EMPTY));
    expect(screen.queryByTestId('edit-rules-input-1-value')).toBeNull();
  });
});
