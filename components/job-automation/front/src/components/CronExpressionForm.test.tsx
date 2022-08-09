import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {CronExpressionForm} from './CronExpressionForm';

jest.mock('./FrequencyConfigurator/WeeklyFrequencyConfigurator', () => ({
  WeeklyFrequencyConfigurator: null,
}));

test('it can select a frequency option', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <CronExpressionForm cronExpression="0 0/12 * * *" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  expect(screen.getByText('akeneo.job_automation.scheduling.frequency.every_12_hours')).toBeInTheDocument();

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.weekly'));

  expect(onChange).toHaveBeenCalledWith('0 0 * * 0');
});

test('it can update the frequency time on a daily cron expression', () => {
  const onChange = jest.fn();

  renderWithProviders(
    <CronExpressionForm cronExpression="10 5 * * *" validationErrors={[]} onCronExpressionChange={onChange} />
  );

  const [, , openMinutesButton] = screen.getAllByTitle('pim_common.open');

  userEvent.click(openMinutesButton);
  userEvent.click(screen.getByText('40'));

  expect(onChange).toHaveBeenCalledWith('40 5 * * *');
});

test('it displays a helper when the frequency option is hourly', () => {
  renderWithProviders(
    <CronExpressionForm cronExpression="0 0/8 * * *" validationErrors={[]} onCronExpressionChange={jest.fn()} />
  );

  expect(screen.getByText('akeneo.job_automation.scheduling.frequency.hourly_helper')).toBeInTheDocument();
});

test('it displays validation errors', () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.a_type_error',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[]',
    },
  ];

  renderWithProviders(
    <CronExpressionForm
      cronExpression="0 0/4 * * *"
      validationErrors={validationErrors}
      onCronExpressionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
});

test('it throws when the configurator is not found', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() =>
    renderWithProviders(
      <CronExpressionForm cronExpression="0 0 * * 1" validationErrors={[]} onCronExpressionChange={jest.fn()} />
    )
  ).toThrowError('No frequency configurator found for frequency option "weekly"');

  mockedConsole.mockRestore();
});
