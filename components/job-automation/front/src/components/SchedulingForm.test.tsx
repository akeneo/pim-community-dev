import React from 'react';
import userEvent from '@testing-library/user-event';
import {screen} from '@testing-library/react';
import {renderWithProviders, ValidationError} from '@akeneo-pim-community/shared';
import {SchedulingForm} from './SchedulingForm';
import {Automation} from '../models';

const automation: Automation = {
  is_enabled: true,
  cron_expression: '5 11/4 * * *',
  running_user_groups: [],
};

test('it can select a frequency option', () => {
  const onChange = jest.fn();

  renderWithProviders(<SchedulingForm automation={automation} validationErrors={[]} onAutomationChange={onChange} />);

  expect(screen.getByText('akeneo.job_automation.scheduling.frequency.every_4_hours')).toBeInTheDocument();

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.job_automation.scheduling.frequency.weekly'));

  expect(onChange).toHaveBeenCalledWith({
    ...automation,
    cron_expression: '5 11 * * 0',
  });
});

test('it can update the scheduling time', () => {
  const onChange = jest.fn();

  renderWithProviders(<SchedulingForm automation={automation} validationErrors={[]} onAutomationChange={onChange} />);

  const input = screen.getByDisplayValue('11:05');

  userEvent.clear(input);
  userEvent.type(input, '7:45');

  expect(onChange).toHaveBeenCalledWith({
    ...automation,
    cron_expression: '45 7/4 * * *',
  });
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
    <SchedulingForm automation={automation} validationErrors={validationErrors} onAutomationChange={jest.fn()} />
  );

  expect(screen.getByText('error.key.a_type_error')).toBeInTheDocument();
});
