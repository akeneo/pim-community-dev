import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {act, fireEvent, screen} from '@testing-library/react';
import {SelectRowSelector} from '../../../src';
import {TestAttributeContextProvider} from '../../shared/TestAttributeContextProvider';
import {getComplexTableAttribute} from '../../factories';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/fetchers/SelectOptionsFetcher');
const scroll = mockScroll();

describe('SelectRowSelector', () => {
  it('should display current row', async () => {
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectRowSelector value={'pepper'} onChange={jest.fn()} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
  });

  it('should display all rows, then update it', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectRowSelector value={'pepper'} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();

    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    act(() => scroll());
    expect(screen.getByText('Salt')).toBeInTheDocument();
    expect(screen.getAllByText('Pepper')).toHaveLength(2);
    expect(screen.getByText('[eggs]')).toBeInTheDocument();
    expect(screen.getByText('Sugar')).toBeInTheDocument();
    fireEvent.click(screen.getByText('[eggs]'));
    expect(handleChange).toBeCalledWith('eggs');
  });

  it('should remove current row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectRowSelector value={'pepper'} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.clear_value'));
    expect(handleChange).toBeCalledWith(undefined);
  });

  it('should select any row', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <TestAttributeContextProvider attribute={getComplexTableAttribute()}>
        <SelectRowSelector value={'pepper'} onChange={handleChange} />
      </TestAttributeContextProvider>
    );

    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    act(() => {
      fireEvent.click(screen.getByTitle('pim_common.open'));
    });
    fireEvent.click(screen.getByText('pim_table_attribute.datagrid.any_row'));
    expect(handleChange).toBeCalledWith(null);
  });
});
