import React from 'react';
import {BooleanInput} from './BooleanInput';
import {fireEvent, render, screen} from '../../../storybook/test-util';

test('it renders default component', () => {
  render(<BooleanInput value={true} />);

  expect(screen.getByText('Yes')).toBeInTheDocument();
  expect(screen.getByText('No')).toBeInTheDocument();
});

test('it renders with custom labels', () => {
  render(<BooleanInput value={false} clearable={true} yesLabel={'Oui'} noLabel={'Non'} clearLabel={'Effacer'} />);

  expect(screen.getByText('Oui')).toBeInTheDocument();
  expect(screen.getByText('Non')).toBeInTheDocument();
  expect(screen.getByText('Effacer')).toBeInTheDocument();
});

test('it does not allow clear if this is readonly', () => {
  render(<BooleanInput value={true} clearable={true} readOnly={true} />);

  expect(screen.queryByText('Clear value')).not.toBeInTheDocument();
});

test('it does not allow clear if there is no value', () => {
  render(<BooleanInput value={null} clearable={true} />);

  expect(screen.queryByText('Clear value')).not.toBeInTheDocument();
});

test('it executes callbacks on buttons', () => {
  const onChange = jest.fn();
  render(<BooleanInput value={false} onChange={onChange} clearable={true} />);

  fireEvent.click(screen.getByText('Yes'));
  fireEvent.click(screen.getByText('No'));
  fireEvent.click(screen.getByText('Clear value'));

  expect(onChange).toBeCalledTimes(3);
  expect(onChange).toBeCalledWith(true);
  expect(onChange).toBeCalledWith(false);
  expect(onChange).lastCalledWith(null);
});

test('it does not call callback if readonly', () => {
  const onChange = jest.fn();
  render(<BooleanInput value={false} onChange={onChange} readOnly={true} />);

  fireEvent.click(screen.getByText('Yes'));

  expect(onChange).not.toBeCalled();
});

test('it does not call callback if there is no callback', () => {
  const onChange = jest.fn();
  render(<BooleanInput value={false} />);

  fireEvent.click(screen.getByText('Yes'));

  expect(onChange).not.toBeCalled();
});

test('BooleanInput supports forwardRef', () => {
  const ref = {current: null};

  render(<BooleanInput value={true} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('BooleanInput supports ...rest props', () => {
  const {container} = render(<BooleanInput value={true} data-my-attribute="my_value" />);
  expect(container.querySelector('[data-my-attribute="my_value"]')).toBeInTheDocument();
});
