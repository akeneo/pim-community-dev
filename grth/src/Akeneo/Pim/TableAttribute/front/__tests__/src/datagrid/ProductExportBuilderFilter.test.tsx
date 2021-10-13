import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {defaultFilterValuesMapping, getComplexTableAttribute} from '../../factories';
import {act, screen} from '@testing-library/react';
import {ProductExportBuilderFilter} from '../../../src/datagrid/ProductExportBuilderFilter';
import {fireEvent} from '@testing-library/dom';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

const intersectionObserverMock = () => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

describe('ProductExportBuilderFilter', () => {
  it('should call handleChange', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ProductExportBuilderFilter
        attribute={getComplexTableAttribute()}
        initialDataFilter={{}}
        onChange={handleChange}
        filterValuesMapping={defaultFilterValuesMapping}
      />
    );

    expect(await screen.findByPlaceholderText('pim_table_attribute.datagrid.select_your_operator')).toBeInTheDocument();
    expect(screen.getByPlaceholderText('pim_table_attribute.datagrid.select_your_row')).toBeInTheDocument();
    expect(screen.getByPlaceholderText('pim_table_attribute.datagrid.select_your_column')).toBeInTheDocument();

    // Select column
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[0]);
    });
    expect(screen.getByText('Quantity')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Quantity'));

    // Select row
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[1]);
    });
    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Pepper'));

    // Select operator
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
    });
    expect(screen.getByText('pim_common.operators.>')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.>'));

    // Fill value
    fireEvent.change(screen.getByRole('spinbutton'), {target: {value: '4000'}});

    expect(handleChange).toBeCalledWith({
      column: 'quantity',
      operator: '>',
      row: 'pepper',
      value: '4000',
    });
  });
});
