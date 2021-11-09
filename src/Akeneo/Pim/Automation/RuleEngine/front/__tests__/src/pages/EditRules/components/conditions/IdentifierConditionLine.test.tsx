import React from 'react';
import 'jest-fetch-mock';
import {act, renderWithProviders, screen} from '../../../../../../test-utils';
import {Operator} from '../../../../../../src/models/Operator';
import {IdentifierConditionLine} from '../../../../../../src/pages/EditRules/components/conditions/IdentifierConditionLine';
import userEvent from '@testing-library/user-event';

describe('IdentifierConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the identifier condition line with a text input', async () => {
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'identifier',
            operator: Operator.CONTAINS,
            value: 'test',
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
      <IdentifierConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.fields.identifier')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-1-operator')
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-operator')).toHaveValue(
      Operator.CONTAINS
    );
    expect(
      screen.queryByTestId('edit-rules-input-1-value-selector')
    ).not.toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-1-value-text')
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value-text')).toHaveValue(
      'test'
    );
  });

  it('should display the identifier condition line with a selector input', async () => {
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'identifier',
            operator: Operator.IN_LIST,
            value: ['test123', 'test456'],
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
      <IdentifierConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.fields.identifier')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-1-operator')
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-operator')).toHaveValue(
      Operator.IN_LIST
    );
    expect(
      screen.queryByTestId('edit-rules-input-1-value-text')
    ).not.toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-1-value-selector')
    ).toBeInTheDocument();
    expect(
      screen.getByTestId('edit-rules-input-1-value-selector')
    ).toHaveValue(['test123', 'test456']);
  });

  it('should switch between text input and selector according to the selected operator', async () => {
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'identifier',
            operator: Operator.STARTS_WITH,
            value: 'foobar',
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
      <IdentifierConditionLine
        lineNumber={1}
        currentCatalogLocale={'fr_FR'}
        locales={[]}
        scopes={{}}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.fields.identifier')
    ).toBeInTheDocument();

    const operatorSelector = await screen.findByTestId(
      'edit-rules-input-1-operator'
    );

    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue(Operator.STARTS_WITH);
    expect(screen.queryByTestId('edit-rules-input-1-value-text')).toHaveValue(
      'foobar'
    );

    act(() => userEvent.selectOptions(operatorSelector, Operator.EQUALS));
    expect(operatorSelector).toHaveValue(Operator.EQUALS);
    expect(screen.queryByTestId('edit-rules-input-1-value-text')).toHaveValue(
      'foobar'
    );

    act(() => userEvent.selectOptions(operatorSelector, Operator.NOT_IN_LIST));
    expect(operatorSelector).toHaveValue(Operator.NOT_IN_LIST);
    expect(
      screen.queryByTestId('edit-rules-input-1-value-value')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-input-1-value-selector')
    ).toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-input-1-value-selector')
    ).toHaveValue([]);

    act(() =>
      userEvent.selectOptions(operatorSelector, Operator.DOES_NOT_CONTAIN)
    );
    expect(operatorSelector).toHaveValue(Operator.DOES_NOT_CONTAIN);
    expect(
      screen.queryByTestId('edit-rules-input-1-value-selector')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('edit-rules-input-1-value-text')
    ).toBeInTheDocument();
    expect(screen.queryByTestId('edit-rules-input-1-value-text')).toHaveValue(
      ''
    );
  });
});
