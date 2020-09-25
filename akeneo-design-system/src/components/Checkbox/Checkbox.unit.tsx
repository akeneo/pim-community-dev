import React from 'react';
import {fireEvent, render} from '../../storybook/test-util';
import {Checkbox} from './Checkbox';

it('it calls onChange handler when user clicks on checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked="true" onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith('false');
});

it('it calls onChange handler when user clicks on unchecked checkbox', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked="false" onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).toBeCalledWith('true');
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

  expect(onChange).toBeCalledWith('true');
});

it('it does not call onChange handler when read-only', () => {
  const onChange = jest.fn();
  const {getByText} = render(
    <Checkbox checked="true" readOnly={true} onChange={onChange}>
      Checkbox
    </Checkbox>
  );

  const checkbox = getByText('Checkbox');
  fireEvent.click(checkbox);

  expect(onChange).not.toBeCalled();
});
