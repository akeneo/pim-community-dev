import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {NotEmptyDatagridTableFilter} from '../../../src';
import {act, screen, fireEvent} from '@testing-library/react';
import userEvent from '@testing-library/user-event';

jest.mock('../../../src/fetchers/AttributeFetcher');

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

    expect(await screen.findByText('Nutrition')).toBeInTheDocument();
    expect(screen.getByText('pim_common.all')).toBeInTheDocument();
  });

  it('should display an existing filter', async () => {
    renderWithProviders(
      <NotEmptyDatagridTableFilter
        showLabel={true}
        canDisable={true}
        onDisable={jest.fn()}
        attributeCode={'nutrition'}
        onChange={jest.fn()}
        initialDataFilter={{
          operator: 'NOT EMPTY',
        }}
      />
    );

    expect(await screen.findByText('Nutrition')).toBeInTheDocument();
    // criteria hint
    expect(await screen.findByTitle('pim_common.operators.NOT EMPTY')).toBeInTheDocument();

    await openDropdown();

    const elements = await screen.findAllByTitle('pim_common.operators.NOT EMPTY');
    expect(elements[0]).toBeInTheDocument();
    // select option value
    expect(elements[1]).toHaveAttribute('value', 'NOT EMPTY');
  });

  it('should validate on close', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <NotEmptyDatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{
          operator: 'NOT EMPTY',
        }}
      />
    );

    await openDropdown();

    act(() => {
      fireEvent.click(screen.getByTestId('backdrop'));
    });

    expect(handleChange).toBeCalledWith({
      operator: 'NOT EMPTY',
    });
  });

  it('should fire handleChange with null value', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <NotEmptyDatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();

    act(() => {
      userEvent.click(screen.getByTitle('pim_common.open'));
    });

    const element = await screen.findByTitle('pim_common.operators.NOT EMPTY');
    expect(element).toBeInTheDocument();

    const backdrops = await screen.findAllByTestId('backdrop');
    act(() => {
      fireEvent.click(backdrops[1]);
      fireEvent.click(backdrops[0]);
    });

    expect(handleChange).toBeCalledWith({});
  });

  it('should fire handleChange with correct value', async () => {
    const handleChange = jest.fn();
    renderWithProviders(
      <NotEmptyDatagridTableFilter
        onChange={handleChange}
        showLabel={true}
        attributeCode={'nutrition'}
        canDisable={true}
        onDisable={jest.fn()}
        initialDataFilter={{}}
      />
    );

    await openDropdown();

    act(() => {
      userEvent.click(screen.getByTitle('pim_common.open'));
    });

    const element = await screen.findByTitle('pim_common.operators.NOT EMPTY');
    userEvent.click(element);

    const backdrop = await screen.findByTestId('backdrop');
    act(() => {
      fireEvent.click(backdrop);
    });

    expect(handleChange).toBeCalledWith({
      operator: 'NOT EMPTY',
    });
  });
});
