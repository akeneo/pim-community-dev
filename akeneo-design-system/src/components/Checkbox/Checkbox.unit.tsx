import React from 'react';
import {fireEvent, render} from 'storybook/test-util';
import {Checkbox} from './Checkbox';

it('it calls onChange handler when user clicks on checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(<Checkbox status="checked" onChange={onChange} label="Checkbox" />);

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith('unchecked');
});

it('it calls onChange handler when user clicks on unchecked checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(<Checkbox status="unchecked" onChange={onChange} label="Checkbox" />);

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith('checked');
});

it('it calls onChange handler when user clicks on undetermined checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(<Checkbox status="undetermined" onChange={onChange} label="Checkbox" />);

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith('checked');
});

it('it does not call onChange handler when read-only', () => {
  const onChange = jest.fn();
  const {getByText} = render(<Checkbox status="checked" readOnly={true} onChange={onChange} label="Checkbox" />);

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).not.toBeCalled();
});

it('it cannot be instantiated without handler when not readonly', () => {
  jest.spyOn(console, 'error').mockImplementation(() => {
    // do nothing.
  });

  expect(() => {
    render(<Checkbox status="checked" label="Checkbox" />);
  }).toThrow('A Checkbox element expect an onChange attribute if not readOnly');
});
