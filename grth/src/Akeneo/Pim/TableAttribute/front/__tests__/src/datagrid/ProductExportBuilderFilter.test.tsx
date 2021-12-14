import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {getComplexTableAttribute} from '../../factories';
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

const selectRow = async (row: string) => {
  act(() => {
    fireEvent.click(screen.getAllByTitle('pim_common.open')[0]);
  });
  expect(await screen.findByText(row)).toBeInTheDocument();
  fireEvent.click(screen.getByText(row));
};

const selectColumn = (column: string) => {
  act(() => {
    fireEvent.click(screen.getAllByTitle('pim_common.open')[1]);
  });
  expect(screen.getByText(column)).toBeInTheDocument();
  fireEvent.click(screen.getByText(column));
};

const selectOperator = (operator: string) => {
  act(() => {
    fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
  });
  expect(screen.getByText(`pim_common.operators.${operator}`)).toBeInTheDocument();
  fireEvent.click(screen.getByText(`pim_common.operators.${operator}`));
};

describe('ProductExportBuilderFilter', () => {
  it('should call handleChange', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <ProductExportBuilderFilter
        attribute={getComplexTableAttribute()}
        initialDataFilter={{}}
        onChange={handleChange}
      />
    );

    expect(await screen.findByPlaceholderText('pim_table_attribute.datagrid.select_your_operator')).toBeInTheDocument();
    expect(screen.getByPlaceholderText('pim_table_attribute.datagrid.select_your_row')).toBeInTheDocument();
    expect(screen.getByPlaceholderText('pim_table_attribute.datagrid.select_your_column')).toBeInTheDocument();

    await selectRow('Pepper');
    selectColumn('Quantity');
    selectOperator('>');
    fireEvent.change(screen.getByRole('spinbutton'), {target: {value: '4000'}});

    expect(handleChange).toBeCalledWith({
      operator: '>',
      value: {
        row: 'pepper',
        value: '4000',
        column: 'quantity',
      },
    });
  });
});
