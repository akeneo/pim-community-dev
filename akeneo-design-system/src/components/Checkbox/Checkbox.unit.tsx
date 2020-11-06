import React from 'react';
import {fireEvent, render} from '../../storybook/test-util';
import {Checkbox} from './Checkbox';

it('it calls onChange handler when user clicks on checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked={true} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(false, expect.anything());
});

it('it calls onChange handler when user clicks on unchecked checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked={false} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(true, expect.anything());
});

it('it calls onChange handler when user clicks on undetermined checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked="mixed" onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith(true, expect.anything());
  expect(onChange).toBeCalledTimes(1);
});

it('it does not call onChange handler when read-only', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked={true} readOnly={true} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).not.toBeCalled();
});

it('it calls onChange handler when user clicks on checkbox with no label', () => {
  const onChange = jest.fn();
  const {getByTitle} = render(<Checkbox title="nice-checkbox" checked={false} onChange={onChange} />);

  fireEvent.click(getByTitle('nice-checkbox'));

  expect(onChange).toBeCalledWith(true, expect.anything());
  expect(onChange).toBeCalledTimes(1);
});

describe('Checkbox supports forwardRef', () => {
  const ref = {current: null};

  render(<Checkbox checked={false} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

describe('Checkbox supports ...rest props', () => {
  const {container} = render(<Checkbox checked={false} data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
