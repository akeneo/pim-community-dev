import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {TableFieldApp} from '../../../src';
import {TemplateContext} from '../../../src/legacy/table-field';
import {getComplexTableAttribute, getTableValueSelectRow} from '../../factories';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/attribute/LocaleLabel');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
jest.mock('../../../src/product/AddRowsButton');
jest.mock('../../../src/fetchers/MeasurementFamilyFetcher');
mockScroll();

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
    attribute: getComplexTableAttribute(),
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
    expect(await screen.findByText('A')).toBeInTheDocument();

    expect(screen.getByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('en')).toBeInTheDocument();
    expect(screen.getByText('Ecommerce')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.search')).toBeInTheDocument();
    expect(screen.getByTitle('pim_enrich.entity.product.module.attribute.remove_optional')).toBeInTheDocument();
  });

  it('should render elements', async () => {
    const handleChange = jest.fn();

    const html = document.createElement('div');
    html.innerHTML = 'This rule can be updated by <span>2 rules</span>';
    const backbone = document.createElement('div');
    backbone.innerHTML = 'Completeness';

    const elementAsString = '<div>Element as String</div>';
    const elementAsHtml = [html];
    const elementAsBackbone = {
      render: () => {
        return {el: backbone};
      },
    };

    renderWithProviders(
      <TableFieldApp
        {...getTemplateContext()}
        onChange={handleChange}
        elements={{
          badge: {completeness: elementAsString},
          label: {guidelines: elementAsBackbone},
          footer: {from_smart: elementAsHtml},
        }}
        onCopyCheckboxChange={jest.fn()}
      />
    );

    expect(await screen.findByText('Sugar')).toBeInTheDocument();
    expect(await screen.findByText('A')).toBeInTheDocument();
    expect(screen.getByText('Completeness')).toBeInTheDocument();
    expect(screen.getByText('Element as String')).toBeInTheDocument();

    expect(screen.getByText(/This rule can be updated by/)).toBeInTheDocument();
    expect(screen.getByText('2 rules')).toBeInTheDocument();
    fireEvent.click(screen.getByText('2 rules'));
  });

  it('should add and remove a row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TableFieldApp {...getTemplateContext()} onChange={handleChange} elements={{}} onCopyCheckboxChange={jest.fn()} />
    );

    expect(await screen.findByText('Sugar')).toBeInTheDocument();
    expect(await screen.findByText('A')).toBeInTheDocument();
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
    const element = document.createElement('div');
    const elementAsHtml = [element];

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

    expect(screen.queryByText('Salt')).not.toBeInTheDocument();
    expect(container.innerHTML).toEqual('');
  });

  it('should render comparison and callback checkbox change', async () => {
    const handleCopyCheckboxChange = jest.fn();
    const copyContext = {scope: 'mobile', locale: 'fr_FR', data: []};
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
    expect(await screen.findByText('A')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByTestId('copyCheckbox').children[0]);
    });
    expect(handleCopyCheckboxChange).toBeCalledWith(true);
  });
});
