import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {TimeFrequencyConfigurator} from './TimeFrequencyConfigurator';

test('it displays a hours select input that can update a cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <TimeFrequencyConfigurator cronExpression="50 11 * * *" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  const [openHoursButton] = screen.getAllByTitle('pim_common.open');

  userEvent.click(openHoursButton);
  userEvent.click(screen.getByText('07'));

  expect(onChange).toHaveBeenLastCalledWith('50 7 * * *');
});

test('it displays a minutes select input that can update a cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <TimeFrequencyConfigurator cronExpression="50 11 * * *" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  const [, openMinutesButton] = screen.getAllByTitle('pim_common.open');

  userEvent.click(openMinutesButton);
  userEvent.click(screen.getByText('40'));

  expect(onChange).toHaveBeenLastCalledWith('40 11 * * *');
});
