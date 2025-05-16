import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Avatar} from './Avatar';

test('renders', () => {
  render(<Avatar username="john" firstName="John" lastName="Doe" />);

  const avatar = screen.getByTitle('John Doe');
  expect(avatar).toBeInTheDocument();
});

test('avatar image', () => {
  render(<Avatar username="john" firstName="John" lastName="Doe" avatarUrl="path/to/image" />);

  const avatar = screen.getByTitle('John Doe');
  expect(avatar).toHaveStyle('background-image: url(path/to/image)');
});

test('deterministic fallback color', () => {
  render(<Avatar username="john" firstName="John" lastName="Doe" />);

  const avatar = screen.getByTitle('John Doe');
  expect(avatar).toHaveStyle('background-color: rgb(68, 31, 0)');
});

test('fallback to firstname + lastname', () => {
  render(<Avatar username="" firstName="John" lastName="Doe" />);

  const avatar = screen.getByTitle('John Doe');
  expect(avatar).toHaveTextContent('JD');
});

test('fallback to firstname only', () => {
  render(<Avatar username="" firstName="John" lastName="" />);

  const avatar = screen.getByTitle('John');
  expect(avatar).toHaveTextContent('J');
});

test('fallback to lastname only', () => {
  render(<Avatar username="" firstName="" lastName="Doe" />);

  const avatar = screen.getByTitle('Doe');
  expect(avatar).toHaveTextContent('D');
});

test('fallback to username', () => {
  render(<Avatar username="john" firstName="" lastName="" />);

  const avatar = screen.getByTitle('john');
  expect(avatar).toHaveTextContent('JO');
});

test('initial are converted to uppercase', () => {
  render(<Avatar username="" firstName="john" lastName="doe" />);

  const avatar = screen.getByTitle('john doe');
  expect(avatar).toHaveTextContent('JD');
});

test('size default', () => {
  render(<Avatar username="john" firstName="John" lastName="Doe" />);

  const avatar = screen.getByTitle('John Doe');
  expect(avatar).toHaveStyle('width: 32px');
});

test('size big', () => {
  render(<Avatar username="john" firstName="John" lastName="Doe" size="big" />);

  const avatar = screen.getByTitle('John Doe');
  expect(avatar).toHaveStyle('width: 140px');
});

test('supports ...rest props', () => {
  render(<Avatar username="john" firstName="John" lastName="Doe" data-testid="my_value" />);

  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
