import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {SelectPeriodInput} from './SelectPeriodInput';
import {PredefinedPeriods, PredefinedPeriod} from '../../Common';
import userEvent from '@testing-library/user-event';

describe('SelectPeriodInput', () => {
  it('renders its children properly', async () => {
    await renderWithProviders(
      <SelectPeriodInput filters={PredefinedPeriods} onChange={() => {}} value={PredefinedPeriod.LAST_12_WEEKS} />
    );

    expect(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_WEEKS
      )
    ).toBeInTheDocument();
  });

  it('displays all values when opening the dropdown', async () => {
    const handleOnChange = jest.fn();

    await renderWithProviders(
      <SelectPeriodInput
        filters={PredefinedPeriods}
        onChange={handleOnChange}
        value={PredefinedPeriod.LAST_12_MONTHS}
      />
    );

    userEvent.click(screen.getByRole('textbox'));

    expect(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_WEEKS
      )
    ).toBeInTheDocument();
    expect(
      screen.getAllByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_MONTHS
      )
    ).toHaveLength(2);
  });

  it('calls onChange handler when selecting another value', async () => {
    const handleOnChange = jest.fn();

    await renderWithProviders(
      <SelectPeriodInput filters={PredefinedPeriods} onChange={handleOnChange} value={PredefinedPeriod.LAST_12_WEEKS} />
    );

    userEvent.click(screen.getByRole('textbox'));
    userEvent.click(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.periods.' + PredefinedPeriod.LAST_12_MONTHS
      )
    );

    expect(handleOnChange).toHaveBeenCalledWith(PredefinedPeriod.LAST_12_MONTHS);
  });
});
