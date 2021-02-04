import React from 'react';
import {NumberInput} from './NumberInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <NumberInput id="myInput" value="13" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;

  fireEvent.change(input, {target: {value: '12'}});
  expect(handleChange).toHaveBeenCalledWith('12');
});

test('it renders and handle changes on up/down buttons', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <NumberInput id="myInput" value="12" onChange={handleChange} />
    </>
  );

  expect(screen.getByTestId('increment-number-input')).toBeInTheDocument();
  const increment = screen.getByTestId('increment-number-input');
  fireEvent.click(increment);
  expect(handleChange).toHaveBeenCalledWith('13');

  expect(screen.getByTestId('decrement-number-input')).toBeInTheDocument();
  const decrement = screen.getByTestId('decrement-number-input');
  fireEvent.click(decrement);
  expect(handleChange).toHaveBeenCalledWith('12');
});

test('it renders and does not call onChange if readOnly', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <NumberInput id="myInput" readOnly={true} value="12" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: 'Cool'}});
  expect(handleChange).not.toHaveBeenCalledWith('Cool');

  expect(screen.queryByTestId('increment-number-input')).not.toBeInTheDocument();
  expect(screen.queryByTestId('decrement-number-input')).not.toBeInTheDocument();
});

test('NumberInput supports forwardRef', () => {
  const ref = {current: null};

  render(<NumberInput value="12" onChange={jest.fn()} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('NumberInput supports ...rest props', () => {
  render(<NumberInput value="12" onChange={jest.fn()} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
