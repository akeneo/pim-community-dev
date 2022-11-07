import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {SelectAggregationInput} from './SelectAggregationInput';
import {Aggregation, Aggregations} from '../../Common';
import userEvent from '@testing-library/user-event';

describe('SelectAggregationInput', () => {
  it('renders its children properly', async () => {
    await renderWithProviders(
      <SelectAggregationInput filters={Aggregations} onChange={() => {}} value={Aggregation.FAMILIES} />
    );

    expect(
      screen.getByText('akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.FAMILIES)
    ).toBeInTheDocument();
  });

  it('displays all values when opening the dropdown', async () => {
    const handleOnChange = jest.fn();

    await renderWithProviders(
      <SelectAggregationInput filters={Aggregations} onChange={handleOnChange} value={Aggregation.FAMILIES} />
    );

    userEvent.click(screen.getByRole('textbox'));

    expect(
      screen.getByText('akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.CATEGORIES)
    ).toBeInTheDocument();
    expect(
      screen.getAllByText(
        'akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.FAMILIES
      )
    ).toHaveLength(2);
  });

  it('calls onChange handler when selecting another value', async () => {
    const handleOnChange = jest.fn();

    await renderWithProviders(
      <SelectAggregationInput filters={Aggregations} onChange={handleOnChange} value={Aggregation.FAMILIES} />
    );

    userEvent.click(screen.getByRole('textbox'));
    userEvent.click(
      screen.getByText('akeneo.performance_analytics.control_panel.select_input.aggregations.' + Aggregation.CATEGORIES)
    );

    expect(handleOnChange).toHaveBeenCalledWith(Aggregation.CATEGORIES);
  });
});
