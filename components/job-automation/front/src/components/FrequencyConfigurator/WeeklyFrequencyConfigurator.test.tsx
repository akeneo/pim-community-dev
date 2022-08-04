import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {WeeklyFrequencyConfigurator} from './WeeklyFrequencyConfigurator';

test('it can select a weekday', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <WeeklyFrequencyConfigurator cronExpression="50 11 * * 1" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  expect(screen.getByText('akeneo.job_automation.scheduling.frequency.monday')).toBeInTheDocument();

  const [openWeekDayButton] = screen.getAllByTitle('pim_common.open');

  userEvent.click(openWeekDayButton);
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.thursday'));

  expect(onChange).toHaveBeenCalledWith('50 11 * * 4');

  userEvent.click(openWeekDayButton);
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.saturday'));

  expect(onChange).toHaveBeenCalledWith('50 11 * * 6');
});

test('it displays a time input that can update a weekly cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <WeeklyFrequencyConfigurator cronExpression="50 11 * * 0" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  const [, , openMinutesButton] = screen.getAllByTitle('pim_common.open');

  userEvent.click(openMinutesButton);
  userEvent.click(screen.getByText('00'));

  expect(onChange).toHaveBeenCalledWith('0 11 * * 0');
});
