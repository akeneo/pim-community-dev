import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {DatagridTableFilter} from '../../../src';
import {act, fireEvent, screen} from '@testing-library/react';
import {mockScroll} from '../../shared/mockScroll';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');
const scroll = mockScroll();

const openDropdown = async () => {
  expect(await screen.findByText('Nutrition')).toBeInTheDocument();
  act(() => {
    fireEvent.click(screen.getByText('Nutrition'));
  });
};

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

describe('DatagridTableFilter', () => {
  it('should display a filter', async () => {
    renderWithProviders(
      <DatagridTableFilter
        onChange={jest.fn()}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    expect(await screen.findByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('pim_common.all')).toBeInTheDocument();
  });

  it('should display an existing filter', async () => {
    renderWithProviders(
      <DatagridTableFilter
        onChange={jest.fn()}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{
          value: 10000,
          row: 'salt',
          operator: '>=',
          column: 'quantity',
        }}
      />
    );

    expect(await screen.findByText('Nutrition')).toBeInTheDocument();
    expect(await screen.findByTitle('Salt Quantity pim_common.operators.>= 10000')).toBeInTheDocument();

    await openDropdown();

    expect(screen.getByTitle('Quantity')).toBeInTheDocument();
    expect(await screen.findByTitle('Salt')).toBeInTheDocument();
    expect(screen.getByTitle('pim_common.operators.>=')).toBeInTheDocument();
    expect(screen.getByTitle('10000')).toBeInTheDocument();
  });

  it('should validate on close', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{
          value: 10000,
          row: 'salt',
          operator: '>=',
          column: 'quantity',
        }}
      />
    );

    await openDropdown();

    act(() => {
      fireEvent.click(screen.getByTestId('backdrop'));
    });

    expect(handleChange).toBeCalledWith({
      value: 10000,
      row: 'salt',
      operator: '>=',
      column: 'quantity',
    });
  });

  it('should reset filter when invalid', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{
          value: 10000,
          row: 'salt',
          operator: '>=',
          column: 'quantity',
        }}
      />
    );

    await openDropdown();

    // Select operator to clear the value and make the filter invalid
    selectOperator('>');

    act(() => {
      fireEvent.click(screen.getByTestId('backdrop'));
    });
    expect(handleChange).toBeCalledWith({});
  });

  it('should callback changes with number', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();
    await selectRow('pim_table_attribute.datagrid.any_row');
    selectColumn('Quantity');
    await selectRow('Pepper');
    selectOperator('>');
    fireEvent.change(screen.getByRole('spinbutton'), {target: {value: '4000'}});
    fireEvent.click(screen.getByText('pim_common.update'));

    expect(screen.getByTitle('Pepper Quantity pim_common.operators.> 4000')).toBeInTheDocument();
    expect(handleChange).toBeCalledWith({
      column: 'quantity',
      operator: '>',
      row: 'pepper',
      value: '4000',
    });
  });

  it('should callback changes with boolean', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();
    await selectRow('pim_table_attribute.datagrid.any_row');
    selectColumn('Is allergenic');
    selectOperator('=');
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[3]);
    });
    expect(screen.getByText('pim_common.yes')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.yes'));
    fireEvent.click(screen.getByText('pim_common.update'));

    expect(
      screen.getByTitle('pim_table_attribute.datagrid.any Is allergenic pim_common.operators.= pim_common.yes')
    ).toBeInTheDocument();
    expect(handleChange).toBeCalledWith({
      column: 'is_allergenic',
      operator: '=',
      value: true,
    });
  });

  it('should callback changes with string', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();
    await selectRow('pim_table_attribute.datagrid.any_row');
    selectColumn('For 1 part');
    selectOperator('STARTS WITH');
    fireEvent.change(screen.getAllByRole('textbox')[3], {target: {value: 'foo'}});
    fireEvent.click(screen.getByText('pim_common.update'));

    expect(
      screen.getByTitle('pim_table_attribute.datagrid.any For 1 part pim_common.operators.STARTS WITH "foo"')
    ).toBeInTheDocument();
    expect(handleChange).toBeCalledWith({
      column: 'part',
      operator: 'STARTS WITH',
      value: 'foo',
    });
  });

  it('should callback changes with empty value', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();
    await selectRow('pim_table_attribute.datagrid.any_row');
    selectColumn('Is allergenic');
    selectOperator('EMPTY');
    fireEvent.click(screen.getByText('pim_common.update'));

    expect(
      screen.getByTitle('pim_table_attribute.datagrid.any Is allergenic pim_common.operators.EMPTY')
    ).toBeInTheDocument();

    expect(handleChange).toBeCalledWith({
      column: 'is_allergenic',
      operator: 'EMPTY',
    });
  });

  it('should callback changes with select options', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <DatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();
    await selectRow('pim_table_attribute.datagrid.any_row');
    selectColumn('Ingredients');
    selectOperator('IN');
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[3]);
    });
    expect(await screen.findByText('Salt')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Salt'));
    act(() => scroll());
    expect(await screen.findByText('Pepper')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Pepper'));
    fireEvent.click(screen.getByText('pim_common.update'));

    expect(
      await screen.findByTitle('pim_table_attribute.datagrid.any Ingredients pim_common.operators.IN Salt, Pepper')
    ).toBeInTheDocument();
    expect(handleChange).toBeCalledWith({
      column: 'ingredient',
      operator: 'IN',
      value: ['salt', 'pepper'],
    });
  });
});
