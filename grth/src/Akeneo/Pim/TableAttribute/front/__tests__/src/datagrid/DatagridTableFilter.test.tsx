import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {defaultFilterValuesMapping} from '../../factories';
import {DatagridTableFilter} from '../../../src/datagrid';
import {act, fireEvent, screen} from '@testing-library/react';

jest.mock('../../../src/fetchers/AttributeFetcher');
jest.mock('../../../src/fetchers/SelectOptionsFetcher');

type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
  observe: jest.fn(() => (entryCallback = callback)),
  unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

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
    expect(screen.getByText('pim_common.all')).toBeInTheDocument();
  });

  it('should callback changes with number', async () => {
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
    expect(screen.getByText('Is allergenic')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Is allergenic'));

    // Select operator
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
    });
    expect(screen.getByText('pim_common.operators.=')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.='));

    // Select value
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
    expect(screen.getByText('For 1 part')).toBeInTheDocument();
    fireEvent.click(screen.getByText('For 1 part'));

    // Select operator
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
    });
    expect(screen.getByText('pim_common.operators.STARTS WITH')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.STARTS WITH'));

    // Select value
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
    expect(screen.getByText('Is allergenic')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Is allergenic'));

    // Select operator
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
    });
    expect(screen.getByText('pim_common.operators.EMPTY')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.EMPTY'));

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
    expect(screen.getByText('Ingredients')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Ingredients'));

    // Select operator
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[2]);
    });
    expect(screen.getByText('pim_common.operators.IN')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_common.operators.IN'));

    // Select value
    act(() => {
      fireEvent.click(screen.getAllByTitle('pim_common.open')[3]);
    });
    expect(await screen.findByText('Salt')).toBeInTheDocument();
    fireEvent.click(screen.getByText('Salt'));
    act(() => {
      entryCallback?.([{isIntersecting: true}]);
    });
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
