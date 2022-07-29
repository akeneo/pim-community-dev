import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {HourlyFrequencyConfigurator} from './HourlyFrequencyConfigurator';

test('it displays a time input that can update a hourly cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <HourlyFrequencyConfigurator
      cronExpression="5 11/8 * * *"
      validationErrors={[]}
      onCronExpressionChange={onChange}
    />
  );

  const input = screen.getByDisplayValue('11:05');

  userEvent.clear(input);
  userEvent.type(input, '7:45');

  expect(onChange).toHaveBeenCalledWith('45 7/8 * * *');
});
