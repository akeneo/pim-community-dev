import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {ClearIfEmpty} from './ClearIfEmpty';
import {MeasurementTarget} from '../Attribute';

const skipValueTarget: MeasurementTarget = {
  code: 'power',
  type: 'attribute',
  locale: null,
  channel: null,
  source_configuration: {
    decimal_separator: ',',
    unit: 'WATT',
  },
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
};

const clearValueTarget: MeasurementTarget = {
  code: 'power',
  type: 'attribute',
  locale: null,
  channel: null,
  source_configuration: {
    decimal_separator: ',',
    unit: 'WATT',
  },
  action_if_not_empty: 'set',
  action_if_empty: 'clear',
};

test('it can clear the target value if value is empty', () => {
  const handleChange = jest.fn();
  renderWithProviders(<ClearIfEmpty target={skipValueTarget} onTargetChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(handleChange).toHaveBeenCalledWith({
    ...skipValueTarget,
    action_if_empty: 'clear',
  });
});

test('it can skip the product if value is empty', () => {
  const handleChange = jest.fn();
  renderWithProviders(<ClearIfEmpty target={clearValueTarget} onTargetChange={handleChange} />);

  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(handleChange).toHaveBeenCalledWith({
    ...clearValueTarget,
    action_if_empty: 'skip',
  });
});
