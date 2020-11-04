import React from 'react';
import {renderWithProviders, screen} from '../../../../../../test-utils';
import {Operator} from '../../../../../../src/models/Operator';
import {EntityType} from '../../../../../../src/models/conditions';
import {EntityTypeConditionLine} from '../../../../../../src/pages/EditRules/components/conditions/EntityTypeConditionLine';

describe('EntityTypeConditionLine', () => {
  it('should display the entity_type condition line', () => {
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'entity_type',
            operator: Operator.EQUALS,
            value: EntityType.PRODUCT_MODEL,
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
      <EntityTypeConditionLine
        lineNumber={1}
        locales={[]}
        scopes={{}}
        currentCatalogLocale={'fr_FR'}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    expect(
      screen.getByText('pimee_catalog_rule.form.edit.fields.entity_type.label')
    ).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('=');
    const statusSelector = screen.getByTestId('edit-rules-input-1-value');
    expect(statusSelector).toBeInTheDocument();
    expect(statusSelector).toHaveValue(
      'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\ProductModelInterface'
    );
  });
});
