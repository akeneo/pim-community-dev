import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, act, fireEvent} from '@testing-library/react';
import {TableFieldApp} from '../../../src/product/TableFieldApp';
import {TemplateContext} from '../../../src/legacy/table-field';
import {getComplexTableConfiguration} from '../factories/TableConfiguration';
import {getTableValueSelectRow} from '../factories/TableValue';
jest.mock('../../../src/attribute/LocaleLabel');

const getTemplateContext: () => TemplateContext = () => {
  return {
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
      table_configuration: getComplexTableConfiguration(),
    },
    value: {data: [getTableValueSelectRow()]},
    editMode: 'edit',
  };
};

describe('TableFieldApp', () => {
  it('should render the component', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableFieldApp {...getTemplateContext()} onChange={handleChange} elements={{}} />);

    expect(screen.getByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('en')).toBeInTheDocument();
    expect(screen.getByText('Ecommerce')).toBeInTheDocument();
    expect(screen.getByTitle('Search')).toBeInTheDocument();
    expect(screen.getByTitle('pim_enrich.entity.product.module.attribute.remove_optional')).toBeInTheDocument();
  });

  it('should render elements', () => {
    const handleChange = jest.fn();

    const elementAsHtml = [
      {
        outerHTML: '<div>Guidelines</div>',
      },
    ];
    const elementAsBackbone = {
      render: () => {
        return {el: {innerHTML: '<div>Completeness</div>'}};
      },
    };

    renderWithProviders(
      <TableFieldApp
        {...getTemplateContext()}
        onChange={handleChange}
        elements={{
          footer: {guidelines: elementAsHtml},
          badge: {completeness: elementAsBackbone},
        }}
      />
    );

    expect(screen.getByText('Guidelines')).toBeInTheDocument();
    expect(screen.getByText('Completeness')).toBeInTheDocument();
  });

  it('should add a row', () => {
    const handleChange = jest.fn();
    renderWithProviders(<TableFieldApp {...getTemplateContext()} onChange={handleChange} elements={{}} />);

    const addRowButton = screen.getByText('Add row');
    act(() => {
      fireEvent.click(addRowButton);
    });
    expect(handleChange).toBeCalledWith([getTableValueSelectRow(), {}]);
  });
});
