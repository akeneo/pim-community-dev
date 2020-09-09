import React from 'react';
import {fireEvent, render} from '../../shared/test-util';
import {Checkbox} from './Checkbox';

it('it calls onChange handler when user clicks on checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked={true} onChange={onChange} label="Checkbox"/>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(false);
});

it('it does not call onChange handler when read-only', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked={true} readOnly={true} onChange={onChange} label="Checkbox"/>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).not.toBeCalled();
});

it('it cannot be instantiated without handler when not readonly', () => {
  expect(() => {
    render(<Checkbox checked={true} label="Checkbox"/>);
  }).toThrow('A Checkbox element expect an onChange attribute if not readOnly');
});
