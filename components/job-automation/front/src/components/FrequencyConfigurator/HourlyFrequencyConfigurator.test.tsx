import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {HourlyFrequencyConfigurator} from './HourlyFrequencyConfigurator';

test('it displays a time input that can update a hourly cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <HourlyFrequencyConfigurator
      frequencyOption="every_12_hours"
      cronExpression="5 11,23 * * *"
      validationErrors={[]}
      onCronExpressionChange={onChange}
    />
  );

  const input = screen.getByDisplayValue('11:05');

  userEvent.clear(input);
  userEvent.type(input, '7:45');

  expect(onChange).toHaveBeenLastCalledWith('45 7,19 * * *');
});
