import React from 'react';
import {MainNavigationItem} from './MainNavigationItem';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {CardIcon} from '../../../icons';
import {Tag} from '../../Tags/Tags';

test('MainNavigationItem supports forwardRef', () => {
  const ref = {current: null};

  render(
    <MainNavigationItem icon={<CardIcon />} ref={ref}>
      My title
    </MainNavigationItem>
  );
  expect(ref.current).not.toBe(null);
});

test('MainNavigationItem supports ...rest props', () => {
  render(
    <MainNavigationItem icon={<CardIcon />} data-testid="my_value">
      My title
    </MainNavigationItem>
  );
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('MainNavigationItem displays title', () => {
  render(<MainNavigationItem icon={<CardIcon />}>My title</MainNavigationItem>);
  expect(screen.getByText('My title')).toBeInTheDocument();
});

test('MainNavigationItem displays tags', () => {
  render(
    <MainNavigationItem icon={<CardIcon />}>
      My title <Tag tint="blue">My tag</Tag>
    </MainNavigationItem>
  );
  expect(screen.getByText('My title')).toBeInTheDocument();
  expect(screen.getByText('My tag')).toBeInTheDocument();
});

test('MainNavigationItem triggers onClick when MainNavigationItem is clicked', () => {
  const onClick = jest.fn();

  render(
    <MainNavigationItem icon={<CardIcon />} onClick={onClick}>
      My title
    </MainNavigationItem>
  );

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).toBeCalled();
});

test('MainNavigationItem will not trigger onClick when MainNavigationItem is disabled', () => {
  const onClick = jest.fn();

  render(
    <MainNavigationItem icon={<CardIcon />} onClick={onClick} disabled={true}>
      My title
    </MainNavigationItem>
  );

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).not.toBeCalled();
});

test('MainNavigationItem will not trigger onClick when onClick is undefined', () => {
  const onClick = jest.fn();

  render(
    <MainNavigationItem icon={<CardIcon />} onClick={undefined}>
      My title
    </MainNavigationItem>
  );

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).not.toBeCalled();
});

test('MainNavigationItem displays an anchor when providing a `href`', () => {
  render(
    <MainNavigationItem icon={<CardIcon />} href="https://akeneo.com/">
      My title
    </MainNavigationItem>
  );
  expect(screen.getByText('My title').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('MainNavigationItem do not display an anchor when providing a `href` on a disabled component', () => {
  render(
    <MainNavigationItem icon={<CardIcon />} href="https://akeneo.com/" disabled={true}>
      My title
    </MainNavigationItem>
  );

  expect(screen.getByText('My title').closest('a')).not.toHaveAttribute('href');
});
