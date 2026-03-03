import userEvent from '@testing-library/user-event';
import React from 'react';
import {renderWithProviders} from '../tests/utils';
import {TextField} from './TextField';

test('it should render a text field', () => {
  const handleChange = jest.fn();

  const {getByLabelText} = renderWithProviders(<TextField value="nice" onChange={handleChange} label="Input label" />);

  userEvent.type(getByLabelText('Input label'), '!');

  expect(handleChange).toHaveBeenCalledWith('nice!');
});

test('it display validation errors render a text field', () => {
  const handleChange = jest.fn();

  const {getByText} = renderWithProviders(
    <TextField
      value="nice"
      onChange={handleChange}
      label="Input label"
      errors={[{propertyPath: '', message: 'message', messageTemplate: 'message', parameters: {}, invalidValue: ''}]}
    />
  );

  expect(getByText('message')).toBeInTheDocument();
});

test('it displays that it is required', () => {
  const handleChange = jest.fn();

  const {getByText} = renderWithProviders(
    <TextField value="nice" onChange={handleChange} label="Input label" required />
  );

  expect(getByText('Input label pim_common.required_label')).toBeInTheDocument();
});
