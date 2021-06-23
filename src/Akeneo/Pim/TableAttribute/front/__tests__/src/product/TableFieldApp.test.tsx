import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, act, fireEvent} from '@testing-library/react';
import { TableFieldApp } from "../../../src/product/TableFieldApp";
import { TableConfiguration } from "../../../src/models/TableConfiguration";
import { TemplateContext } from "../../../src/legacy/table-field";
import { TableValue } from "../../../src/models/TableValue";
jest.mock('../../../src/attribute/LocaleLabel');

const tableConfiguration: TableConfiguration = [
  { code: 'ingredient', labels: { 'en_US': 'Ingredients' }, validations: {}, data_type: 'select' },
  { code: 'quantity', labels: {}, validations: {}, data_type: 'number' },
  { code: 'aqr', labels: {}, validations: {}, data_type: 'text' },
  { code: 'is_allergenic', labels: {}, validations: {}, data_type: 'boolean' },
];

const value: TableValue = [
  { ingredient: 'sugar', quantity: 100, aqr: 'Not good', is_allergenic: true },
];

const templateContext: TemplateContext = {
  type: 'akeneo-table-field',
  context: {
    optional: true,
    removable: true,
    scopeLabel: 'Ecommerce',
  },
  label: 'Nutrition',
  locale: 'en_US',
  fieldId: 'foobar',
  scope: 'ecommerce',
  attribute: {
    code: 'nutrition',
    table_configuration: tableConfiguration
  },
  value: {data: value},
  editMode: 'edit',
}

describe('TableFieldApp', () => {
  it('should render the component', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableFieldApp
      {...templateContext}
      onChange={handleChange}
      elements={{}}
    />);

    expect(screen.getByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('en')).toBeInTheDocument();
    expect(screen.getByText('Ecommerce')).toBeInTheDocument();
    expect(screen.getByTitle('Search')).toBeInTheDocument();
    expect(screen.getByTitle('pim_enrich.entity.product.module.attribute.remove_optional')).toBeInTheDocument();
  });

  it('should render elements', () => {
    const handleChange = jest.fn();

    renderWithProviders(<TableFieldApp
      {...templateContext}
      onChange={handleChange}
      elements={{
        footer: {
          guidelines: [
            {
              outerHTML: '<div>Guidelines</div>'
            }
          ]
        },
        badge: {
          completeness: {
            render: () => {
              return {
                el: {
                  innerHTML: '<div>Completeness</div>'
                }
              }
            }
          }
        }
      }}
    />);

    expect(screen.getByText('Guidelines')).toBeInTheDocument();
    expect(screen.getByText('Completeness')).toBeInTheDocument();
  });

  it('should add a row', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableFieldApp
      {...templateContext}
      onChange={handleChange}
      elements={{}}
    />);

    const addRowButton = screen.getByText('Add row');
    act(() => {
      fireEvent.click(addRowButton);
    });
    expect(handleChange).toBeCalledWith([{ ingredient: 'sugar', quantity: 100, aqr: 'Not good', is_allergenic: true }, {}]);
  });
});
