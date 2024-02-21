import React from 'react';
import {ColorInput} from './ColorInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <ColorInput id="myInput" value="#ff0000" onChange={handleChange} />
    </>
  );

  fireEvent.change(screen.getByLabelText('My label'), {target: {value: '#00ff00'}});

  expect(handleChange).toHaveBeenCalledWith('#00ff00');
});

test('it renders and does not call onChange if readOnly', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <ColorInput id="myInput" readOnly={true} value="#ff0000" onChange={handleChange} />
    </>
  );

  fireEvent.change(screen.getByLabelText('My label'), {target: {value: '#00ff00'}});

  expect(handleChange).not.toHaveBeenCalledWith('#00ff00');
});

test('it displays an error icon when the color is invalid', () => {
  render(
    <>
      <label htmlFor="myInput">My label</label>
      <ColorInput id="myInput" value="not a valid color" />
    </>
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
});

test('it accepts color without a leading #', () => {
  render(
    <>
      <label htmlFor="myInput">My label</label>
      <ColorInput id="myInput" value="00ff00" />
    </>
  );

  expect(screen.queryByRole('alert')).not.toBeInTheDocument();
});

test('ColorInput supports forwardRef', () => {
  const ref = {current: null};

  render(<ColorInput value="#ff0000" onChange={jest.fn()} ref={ref} />);

  expect(ref.current).not.toBe(null);
});

test('ColorInput supports ...rest props', () => {
  render(<ColorInput value="#ff0000" onChange={jest.fn()} data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
