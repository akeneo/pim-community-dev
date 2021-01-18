import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Checkbox} from './Checkbox';

it('it calls onChange handler when user clicks on checkbox', () => {
  const onChange = jest.fn();
  render(
    <Checkbox checked={true} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = screen.getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(false, expect.anything());
});

it('it calls onChange handler when user clicks on unchecked checkbox', () => {
  const onChange = jest.fn();
  render(
    <Checkbox checked={false} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = screen.getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(true, expect.anything());
});

it('it calls onChange handler when user clicks on undetermined checkbox', () => {
  const onChange = jest.fn();
  render(
    <Checkbox checked="mixed" onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = screen.getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(true, expect.anything());
  expect(onChange).toBeCalledTimes(1);
});

it('it does not call onChange handler when read-only', () => {
  const onChange = jest.fn();
  render(
    <Checkbox checked={true} readOnly={true} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = screen.getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).not.toBeCalled();
});

it('it calls onChange handler when user clicks on checkbox with no label', () => {
  const onChange = jest.fn();
  render(<Checkbox title="nice-checkbox" checked={false} onChange={onChange} />);

  fireEvent.click(screen.getByTitle('nice-checkbox'));

  expect(onChange).toBeCalledWith(true, expect.anything());
  expect(onChange).toBeCalledTimes(1);
});

it('it provides a Skeleton version of a Checkbox', () => {
  render(<Checkbox.Skeleton>Skeleton</Checkbox.Skeleton>);

  expect(screen.getByRole('checkbox')).toBeInTheDocument();
});

describe('Checkbox supports forwardRef', () => {
  const ref = {current: null};

  render(<Checkbox checked={false} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('Checkbox supports ...rest props', () => {
  render(<Checkbox checked={false} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
