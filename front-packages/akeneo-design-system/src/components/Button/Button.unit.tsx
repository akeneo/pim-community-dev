import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Button} from './Button';
import userEvent from '@testing-library/user-event';
import {PlusIcon} from '../../icons';

test('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  render(
    <Button size="small" onClick={onClick}>
      My button
    </Button>
  );

  const button = screen.getByText('My button');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});

test('it calls onClick handler when user hits enter key on button', () => {
  const onClick = jest.fn();
  render(
    <Button ghost={true} onClick={onClick}>
      My button
    </Button>
  );

  const button = screen.getByText('My button');
  button.focus();
  userEvent.type(button, '{enter}');

  expect(onClick).toBeCalled();
});

test('it does not call onClick handler when user clicks on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <Button disabled={true} ghost={true} onClick={onClick}>
      My button
    </Button>
  );

  const button = screen.getByText('My button');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not call onClick handler when user hits enter key on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <Button disabled={true} onClick={onClick}>
      My button
    </Button>
  );

  const button = screen.getByText('My button');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

test('it displays an anchor when providing a `href`', () => {
  render(<Button href="https://akeneo.com/">My button</Button>);

  expect(screen.getByText('My button').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('it does not trigger onClick when disabled', () => {
  const onClick = jest.fn();
  render(
    <Button disabled={true} onClick={onClick}>
      My button
    </Button>
  );

  const button = screen.getByText('My button');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not trigger onClick when onClick is undefined', () => {
  const onClick = jest.fn();
  render(<Button onClick={undefined}>My button</Button>);

  fireEvent.click(screen.getByText('My button'));

  expect(onClick).not.toBeCalled();
});

test('Button supports forwardRef', () => {
  const ref = {current: null};

  render(
    <Button onClick={jest.fn()} ref={ref}>
      My button
    </Button>
  );

  expect(ref.current).not.toBe(null);
});

test('Button supports ...rest props', () => {
  render(
    <Button onClick={jest.fn()} data-testid="my_value">
      My button
    </Button>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it renders children with icon', () => {
  render(
    <Button>
      <PlusIcon data-testid="children-icon" /> My button
    </Button>
  );

  expect(screen.getByText('My button')).toBeInTheDocument();
  expect(screen.getByTestId('children-icon')).toBeInTheDocument();
});
