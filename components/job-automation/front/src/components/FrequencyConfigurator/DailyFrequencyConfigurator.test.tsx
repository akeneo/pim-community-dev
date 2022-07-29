import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {DailyFrequencyConfigurator} from './DailyFrequencyConfigurator';

test('it displays a time input that can update a daily cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <DailyFrequencyConfigurator cronExpression="5 11 * * *" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  const input = screen.getByDisplayValue('11:05');

  userEvent.clear(input);
  userEvent.type(input, '7:45');

  expect(onChange).toHaveBeenCalledWith('45 7 * * *');
});
