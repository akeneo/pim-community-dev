import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {BlockButton} from './BlockButton';
import userEvent from '@testing-library/user-event';

test('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton onClick={onClick}>
      Hello
    </BlockButton>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});

test('it calls onClick handler when user hits enter key on button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton onClick={onClick}>
      Hello
    </BlockButton>
  );

  const button = screen.getByText('Hello');
  button.focus();
  userEvent.type(button, '{enter}');

  expect(onClick).toBeCalled();
});

test('it does not call onClick handler when user clicks on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton disabled={true} onClick={onClick}>
      Hello
    </BlockButton>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not call onClick handler when user hits enter key on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <BlockButton disabled={true} onClick={onClick}>
      Hello
    </BlockButton>
  );

  const button = screen.getByText('Hello');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

test('it displays an anchor when providing a `href`', () => {
  render(<BlockButton href="https://akeneo.com/">Hello</BlockButton>);

  expect(screen.getByText('Hello').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('it does not trigger onClick when disabled', () => {
  const onClick = jest.fn();
  render(
    <BlockButton disabled={true} onClick={onClick}>
      Hello
    </BlockButton>
  );

  const button = screen.getByText('Hello');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not trigger onClick when onClick is undefined', () => {
  const onClick = jest.fn();
  render(<BlockButton onClick={undefined}>Hello</BlockButton>);

  fireEvent.click(screen.getByText('Hello'));

  expect(onClick).not.toBeCalled();
});

test('BlockButton supports forwardRef', () => {
  const ref = {current: null};

  render(
    <BlockButton onClick={jest.fn()} ref={ref}>
      My button
    </BlockButton>
  );

  expect(ref.current).not.toBe(null);
});

test('BlockButton supports ...rest props', () => {
  render(
    <BlockButton onClick={jest.fn()} data-testid="my_value">
      My button
    </BlockButton>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
