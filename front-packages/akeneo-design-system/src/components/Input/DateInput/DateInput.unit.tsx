import React, {RefObject} from 'react';
import {DateInput} from './DateInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders and handle changes', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <DateInput id="myInput" value="2023-03-01" onChange={handleChange} />
    </>
  );

  fireEvent.change(screen.getByLabelText('My label'), {target: {value: '2023-03-02'}});

  expect(handleChange).toHaveBeenCalledWith('2023-03-02');
});

test('it handles on submit callback', () => {
  const handleChange = jest.fn();
  const handleSubmit = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <DateInput id="myInput" value="2023-03-01" onChange={handleChange} onSubmit={handleSubmit} />
    </>
  );

  const input = screen.getByLabelText('My label');
  userEvent.type(input, '{enter}');
  expect(handleChange).not.toHaveBeenCalled();
  expect(handleSubmit).toHaveBeenCalled();
});

test('it renders and does not call onChange if readOnly', () => {
  const handleChange = jest.fn();

  render(
    <>
      <label htmlFor="myInput">My label</label>
      <DateInput id="myInput" readOnly={true} value="2023-01-01" onChange={handleChange} />
    </>
  );

  expect(screen.getByLabelText('My label')).toBeInTheDocument();
  const input = screen.getByLabelText('My label') as HTMLInputElement;
  fireEvent.change(input, {target: {value: '2023-01-02'}});
  expect(handleChange).not.toHaveBeenCalledWith('2023-01-02');
});

test('it supports forwardRef', () => {
  const ref = {current: null};

  render(<DateInput value={'2023-03-01'} onChange={jest.fn()} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  render(<DateInput value={'nice'} onChange={jest.fn()} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it supports formatted value yyyy-mm-dd', () => {
  const ref = {current: null} as RefObject<HTMLInputElement>;

  render(<DateInput value={'not a date'} onChange={jest.fn()} ref={ref} />);
  expect(ref.current?.value).toBe('');

  render(<DateInput value={'2023-03-01'} onChange={jest.fn()} ref={ref} />);
  expect(ref.current?.value).toBe('2023-03-01');
});

test('it updates the onChange handler on rerender', () => {
  const handleChange = jest.fn();
  const refreshedHandleChange = jest.fn();
  const props = {id: 'myInput', value: '2023-03-01', 'data-testid': 'myInput'};

  const {rerender} = render(<DateInput {...props} onChange={handleChange} />);
  rerender(<DateInput {...props} onChange={refreshedHandleChange} />);

  fireEvent.change(screen.getByTestId('myInput'), {target: {value: '2023-03-02'}});

  expect(handleChange).not.toHaveBeenCalled();
  expect(refreshedHandleChange).toHaveBeenCalledWith('2023-03-02');
});
