import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {RowSelector} from '../../../src';
import {items} from '../../../src/fetchers/__mocks__/RecordFetcher';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../factories';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/fetchers/RecordFetcher');
const scroll = mockScroll();

describe('ReferenceEntityRowSelector', () => {
  it('should display current row', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('record')}>
        <RowSelector value={items[0]} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
  });

  it('should display all rows, then update it', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('record')}>
        <RowSelector value={items[0]} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    act(() => scroll());
    expect(screen.getAllByText('Lannion')).toHaveLength(2);
    expect(screen.getByTitle('Vannes')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('Vannes'));
    expect(handleChange).toBeCalledWith(items[1]);
  });

  it('should remove current row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('record')}>
        <RowSelector value={items[0]} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.clear_value'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should select any row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute('record')}>
        <RowSelector value={items[0]} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Lannion')).toBeInTheDocument();
    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    fireEvent.click(screen.getByText('pim_table_attribute.datagrid.any_row'));
    expect(handleChange).toBeCalledWith(null);
  });
});
