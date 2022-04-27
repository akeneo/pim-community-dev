import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MultiSelectTarget} from '../Attribute';
import {ActionIfNotEmpty} from './ActionIfNotEmpty';

const setValueTarget: MultiSelectTarget = {
  code: 'brand_collection',
  type: 'attribute',
  locale: null,
  channel: null,
  source_configuration: null,
  action_if_not_empty: 'add',
  action_if_empty: 'skip',
};

const addValueTarget: MultiSelectTarget = {
  code: 'tshirt_style',
  type: 'attribute',
  locale: null,
  channel: null,
  source_configuration: null,
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
};

test('it can set the target value', () => {
  const handleChange = jest.fn();

  renderWithProviders(<ActionIfNotEmpty target={setValueTarget} onTargetChange={handleChange} />);

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.action_if_not_empty.set'));

  expect(handleChange).toHaveBeenCalledWith({
    ...setValueTarget,
    action_if_not_empty: 'set',
  });
});

test('it can add to the target value', () => {
  const handleChange = jest.fn();

  renderWithProviders(<ActionIfNotEmpty target={addValueTarget} onTargetChange={handleChange} />);

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('akeneo.tailored_import.data_mapping.target.action_if_not_empty.add'));

  expect(handleChange).toHaveBeenCalledWith({
    ...addValueTarget,
    action_if_not_empty: 'add',
  });
});
