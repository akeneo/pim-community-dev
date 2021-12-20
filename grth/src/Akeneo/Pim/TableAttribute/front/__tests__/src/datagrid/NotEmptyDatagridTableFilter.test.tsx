import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {NotEmptyDatagridTableFilter} from "../../../src";
import {act, screen, fireEvent} from '@testing-library/react';

const openDropdown = async () => {
  expect(await screen.findByText('Nutrition')).toBeInTheDocument();
  act(() => {
    fireEvent.click(screen.getByText('Nutrition'));
  });
};

describe('NotEmptyDatagridTableFilter', () => {
  it('should display a filter', async () => {
    renderWithProviders(
      <NotEmptyDatagridTableFilter
        showLabel={true}
        canDisable={true}
        onDisable={jest.fn()}
        attributeCode={'nutrition'}
        onChange={jest.fn()}
        initialDataFilter={{}}
      />
    );

    expect(await screen.findByText('pim_table_attribute.datagrid.select_your_operator')).toBeInTheDocument();
  });

  it('should display an existing filter', async () => {
    renderWithProviders(
      <NotEmptyDatagridTableFilter
        showLabel={true}
        canDisable={true}
        onDisable={jest.fn()}
        attributeCode={'nutrition'}
        onChange={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();

    // expect(await screen.findByText('pim_table_attribute.datagrid.select_your_operator')).toBeInTheDocument();
    expect(screen.findByTitle('pim_common.operators.NOT EMPTY')).toBeInTheDocument();
  });
});
