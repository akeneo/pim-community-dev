import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {defaultFilterValuesMapping} from '../../factories';
import {DatagridTableFilter} from '../../../src/datagrid';
import {act, fireEvent, screen} from '@testing-library/react';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

window.IntersectionObserver = jest.fn().mockImplementation(() => ({
  observe: jest.fn(),
  unobserve: jest.fn(),
}));

describe('DatagridTableFilter', () => {
  it('should display a filter', async () => {
    renderWithProviders(
      <DatagridTableFilter
        onChange={jest.fn()}
        showLabel={true}
        label={'Nutrition'}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        filterValuesMapping={defaultFilterValuesMapping}
      />
    );

    expect(await screen.findByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('All')).toBeInTheDocument();
  });

  it('should callback changes', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        label={'Nutrition'}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        filterValuesMapping={defaultFilterValuesMapping}
      />
    );

    // Open dropdown
    expect(await screen.findByText('Nutrition')).toBeInTheDocument();
    act(() => {
      fireEvent.click(screen.getByText('Nutrition'));
    });

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

    fireEvent.click(screen.getByText('pim_common.update'));

    expect(handleChange).toBeCalledWith({
      column: 'quantity',
      operator: '>',
      row: 'pepper',
      value: '4000',
    });
  });
});
