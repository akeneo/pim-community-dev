import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {BadgeButton} from './BadgeButton';
import userEvent from '@testing-library/user-event';
import {Badge} from '../Badge/Badge';

test('it calls onClick handler when user clicks on button', () => {
  const onClick = jest.fn();
  render(<BadgeButton onClick={onClick}>My badge button</BadgeButton>);

  const button = screen.getByText('My badge button');
  fireEvent.click(button);

  expect(onClick).toBeCalled();
});

test('it calls onClick handler when user hits enter key on button', () => {
  const onClick = jest.fn();
  render(<BadgeButton onClick={onClick}>My badge button</BadgeButton>);

  const button = screen.getByText('My badge button');
  button.focus();
  userEvent.type(button, '{enter}');

  expect(onClick).toBeCalled();
});

test('it does not call onClick handler when user hits enter key on a disabled button', () => {
  const onClick = jest.fn();
  render(
    <BadgeButton disabled={true} onClick={onClick}>
      My badge button
    </BadgeButton>
  );

  const button = screen.getByText('My badge button');
  fireEvent.keyDown(button, {key: 'Enter', code: 'Enter'});

  expect(onClick).not.toBeCalled();
});

test('it does not trigger onClick when disabled', () => {
  const onClick = jest.fn();
  render(
    <BadgeButton disabled={true} onClick={onClick}>
      My badge button
    </BadgeButton>
  );

  const button = screen.getByText('My badge button');
  fireEvent.click(button);

  expect(onClick).not.toBeCalled();
});

test('it does not trigger onClick when onClick is undefined', () => {
  const onClick = jest.fn();
  render(<BadgeButton onClick={undefined}>My badge button</BadgeButton>);

  fireEvent.click(screen.getByText('My badge button'));

  expect(onClick).not.toBeCalled();
});

test('it displays an anchor when providing a `href`', () => {
  render(<BadgeButton href="https://akeneo.com/">My badge button</BadgeButton>);

  expect(screen.getByText('My badge button').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('BadgeButton supports forwardRef', () => {
  const ref = {current: null};

  render(
    <BadgeButton onClick={jest.fn()} ref={ref}>
      My badge button
    </BadgeButton>
  );

  expect(ref.current).not.toBe(null);
});

test('BadgeButton supports ...rest props', () => {
  render(
    <BadgeButton onClick={jest.fn()} data-testid="my_value">
      My badge button
    </BadgeButton>
  );

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it renders a badge button with a badge', () => {
  render(
    <BadgeButton onClick={jest.fn()}>
      My badge button
      <Badge level="secondary" data-testid="badge-child">
        Badge
      </Badge>
    </BadgeButton>
  );

  expect(screen.getByText('My badge button')).toBeInTheDocument();
  expect(screen.getByTestId('badge-child')).toBeInTheDocument();
});
