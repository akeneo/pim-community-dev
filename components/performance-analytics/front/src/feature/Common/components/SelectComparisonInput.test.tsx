import React from 'react';
import {renderWithProviders} from '../../tests';
import {screen} from '@testing-library/react';
import {SelectComparisonInput} from './SelectComparisonInput';
import {PredefinedComparison, PredefinedComparisons} from '../../Common';
import userEvent from '@testing-library/user-event';

describe('SelectComparisonInput', () => {
  it('renders its children properly', async () => {
    await renderWithProviders(
      <SelectComparisonInput
        filters={PredefinedComparisons}
        onChange={() => {}}
        value={PredefinedComparison.SAME_PERIOD_LAST_YEAR}
      />
    );

    expect(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.comparisons.' +
          PredefinedComparison.SAME_PERIOD_LAST_YEAR
      )
    ).toBeInTheDocument();
  });

  it('displays all values when opening the dropdown', async () => {
    const handleOnChange = jest.fn();

    await renderWithProviders(
      <SelectComparisonInput
        filters={PredefinedComparisons}
        onChange={handleOnChange}
        value={PredefinedComparison.SAME_PERIOD_LAST_YEAR}
      />
    );

    userEvent.click(screen.getByRole('textbox'));

    expect(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.comparisons.' +
          PredefinedComparison.SAME_PERIOD_JUST_BEFORE
      )
    ).toBeInTheDocument();
    expect(
      screen.getAllByText(
        'akeneo.performance_analytics.control_panel.select_input.comparisons.' +
          PredefinedComparison.SAME_PERIOD_LAST_YEAR
      )
    ).toHaveLength(2);
  });

  it('calls onChange handler when selecting another value', async () => {
    const handleOnChange = jest.fn();

    await renderWithProviders(
      <SelectComparisonInput
        filters={PredefinedComparisons}
        onChange={handleOnChange}
        value={PredefinedComparison.SAME_PERIOD_LAST_YEAR}
      />
    );

    userEvent.click(screen.getByRole('textbox'));
    userEvent.click(
      screen.getByText(
        'akeneo.performance_analytics.control_panel.select_input.comparisons.' +
          PredefinedComparison.SAME_PERIOD_JUST_BEFORE
      )
    );

    expect(handleOnChange).toHaveBeenCalledWith(PredefinedComparison.SAME_PERIOD_JUST_BEFORE);
  });
});
