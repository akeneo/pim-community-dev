import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {WeeklyFrequencyConfigurator} from './WeeklyFrequencyConfigurator';

test('it can select a weekday', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <WeeklyFrequencyConfigurator
      frequencyOption="weekly"
      cronExpression="5 11 * * 1"
      validationErrors={[]}
      onCronExpressionChange={onChange}
    />
  );

  expect(screen.getByText('akeneo.job_automation.scheduling.frequency.monday')).toBeInTheDocument();

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.thursday'));

  expect(onChange).toHaveBeenCalledWith('5 11 * * 4');

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.saturday'));

  expect(onChange).toHaveBeenCalledWith('5 11 * * 6');
});

test('it displays a time input that can update a weekly cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <WeeklyFrequencyConfigurator
      frequencyOption="weekly"
      cronExpression="5 11 * * 0"
      validationErrors={[]}
      onCronExpressionChange={onChange}
    />
  );

  const input = screen.getByDisplayValue('11:05');

  userEvent.clear(input);
  userEvent.type(input, '7:45');

  expect(onChange).toHaveBeenCalledWith('45 7 * * 0');
});
