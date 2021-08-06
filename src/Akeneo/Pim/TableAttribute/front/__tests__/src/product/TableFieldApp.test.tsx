import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {screen, act, fireEvent} from '@testing-library/react';
import {TableFieldApp} from '../../../src/product/TableFieldApp';
import {TemplateContext} from '../../../src/legacy/table-field';
import {getComplexTableConfiguration} from '../factories/TableConfiguration';
import {getTableValueSelectRow} from '../factories/TableValue';
import {getTableAttribute} from '../factories/Attributes';
jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/product/AddRowsButton');

const intersectionObserverMock = () => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const getTemplateContext: () => TemplateContext = () => {
  return {
    type: 'akeneo-table-field',
    context: {
      optional: true,
      removable: true,
      scopeLabel: 'Ecommerce',
      root: null,
    },
    label: 'Nutrition',
    locale: 'en_US',
    fieldId: 'foobar',
    scope: 'ecommerce',
    attribute: {...getTableAttribute(), table_configuration: getComplexTableConfiguration()},
    value: {data: [getTableValueSelectRow()]},
    editMode: 'edit',
  };
};

describe('TableFieldApp', () => {
  it('should render the component', async () => {
    renderWithProviders(
      <TableFieldApp
        {...getTemplateContext()}
        onChange={jest.fn()}
        elements={{}}
        violations={[
          {
            locale: 'en_US',
            scope: 'ecommerce',
            attribute: 'nutrition',
            path: 'values[nutrition-ecommerce-en_US][0].ingredient',
          },
        ]}
        onCopyCheckboxChange={jest.fn()}
      />
    );

    expect(await screen.findByText('Sugar')).toBeInTheDocument();
    expect(screen.getByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('en')).toBeInTheDocument();
    expect(screen.getByText('Ecommerce')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.search')).toBeInTheDocument();
    expect(screen.getByTitle('pim_enrich.entity.product.module.attribute.remove_optional')).toBeInTheDocument();
  });

  it('should render elements', async () => {
    const handleChange = jest.fn();

    const elementAsString = '<div>Element as String</div>';
    const elementAsHtml = [{outerHTML: '<div>Guidelines</div>'}];
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
          badge: {completeness: elementAsString},
          footer: {guidelines: elementAsHtml},
          label: {fromSmart: elementAsBackbone},
        }}
        onCopyCheckboxChange={jest.fn()}
      />
    );

    expect(await screen.findByText('Sugar')).toBeInTheDocument();
    expect(screen.getByText('Guidelines')).toBeInTheDocument();
    expect(screen.getByText('Completeness')).toBeInTheDocument();
    expect(screen.getByText('Element as String')).toBeInTheDocument();
  });

  it('should add and remove a row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableFieldApp {...getTemplateContext()} onChange={handleChange} elements={{}} onCopyCheckboxChange={jest.fn()} />
    );

    expect(await screen.findByText('Sugar')).toBeInTheDocument();
    const addRowButton = screen.getByText('pim_table_attribute.product_edit_form.add_rows');
    // Add pepper
    act(() => {
      fireEvent.click(addRowButton);
    });
    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    expect(handleChange).toBeCalledWith([getTableValueSelectRow(), {ingredient: 'pepper'}]);

    // Remove pepper
    await act(async () => {
      fireEvent.click(addRowButton);
      expect(await screen.findByText('Pepper')).not.toBeInTheDocument();
    });
    expect(handleChange).toBeCalledWith([getTableValueSelectRow()]);

    // Add again pepper
    act(() => {
      fireEvent.click(addRowButton);
    });
    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    expect(handleChange).toBeCalledWith([getTableValueSelectRow(), {ingredient: 'pepper'}]);
  });

  it('should call comparison render without rendering anything', () => {
    const elementAsHtml = [{outerHTML: '<div></div>'}];

    const {container} = renderWithProviders(
      <TableFieldApp
        {...getTemplateContext()}
        onChange={jest.fn()}
        elements={{
          comparison: {nutrition: elementAsHtml},
        }}
        onCopyCheckboxChange={jest.fn()}
      />
    );

    expect(container.innerHTML).toEqual('');
  });

  it('should render comparison and callback checkbox change', async () => {
    const handleCopyCheckboxChange = jest.fn();
    const copyContext = {scope: 'mobile', locale: 'fr_FR', data: [{ingredient: 'salt'}]};
    renderWithProviders(
      <TableFieldApp
        {...getTemplateContext()}
        onChange={jest.fn()}
        copyContext={copyContext}
        onCopyCheckboxChange={handleCopyCheckboxChange}
        elements={{}}
      />
    );

    expect(await screen.findByText('Sugar')).toBeInTheDocument();
    expect(screen.getByText('Salt')).toBeInTheDocument();
    act(() => {
      fireEvent.click(screen.getByTestId('copyCheckbox').children[0]);
    });
    expect(handleCopyCheckboxChange).toBeCalledWith(true);
  });
});
