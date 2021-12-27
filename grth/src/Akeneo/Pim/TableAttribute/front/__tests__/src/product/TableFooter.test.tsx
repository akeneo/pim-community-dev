import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {TableFooter} from '../../../src';

jest.mock('../../../src/attribute/LocaleLabel');

describe('TableFooter', () => {
  it('should render the component', () => {
    const setCurrentPage = jest.fn();
    const setItemsPerPage = jest.fn();
    renderWithProviders(
      <TableFooter
        currentPage={2}
        itemsPerPage={10}
        rowsCount={55}
        setCurrentPage={setCurrentPage}
        setItemsPerPage={setItemsPerPage}
      />
    );

    expect(screen.getByText('pim_table_attribute.form.product.items_per_page:')).toBeInTheDocument();
    expect(screen.getByText('pim_table_attribute.form.product.current_page')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.previous')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.next')).toBeInTheDocument();
  });

  it('should change page', () => {
    const setCurrentPage = jest.fn();
    const setItemsPerPage = jest.fn();
    renderWithProviders(
      <TableFooter
        currentPage={2}
        itemsPerPage={10}
        rowsCount={55}
        setCurrentPage={setCurrentPage}
        setItemsPerPage={setItemsPerPage}
      />
    );

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.previous'));
    });
    expect(setCurrentPage).toBeCalledWith(1);
    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.next'));
    });
    expect(setCurrentPage).toBeCalledWith(3);
  });

  it('should change items per page', () => {
    const setCurrentPage = jest.fn();
    const setItemsPerPage = jest.fn();
    renderWithProviders(
      <TableFooter
        currentPage={2}
        itemsPerPage={10}
        rowsCount={55}
        setCurrentPage={setCurrentPage}
        setItemsPerPage={setItemsPerPage}
      />
    );

    act(() => {
      fireEvent.click(screen.getByText('pim_table_attribute.form.product.items_per_page:'));
    });
    act(() => {
      fireEvent.click(screen.getByText('50'));
    });
    expect(setItemsPerPage).toBeCalledWith(50);
  });
});
