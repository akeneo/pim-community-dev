import React from 'react';
import {MainNavigationItem} from './MainNavigationItem';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {CardIcon} from '../../../icons';
import {Tag} from '../../Tags/Tags';

test('MainNavigationItem is an anchor', () => {
  render(
    <MainNavigationItem icon={<CardIcon />} href="https://akeneo.com/">
      My title
    </MainNavigationItem>
  );
  expect(screen.getByText('My title').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

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

test('MainNavigationItem displays a tag', () => {
  render(
    <MainNavigationItem icon={<CardIcon />}>
      My title <Tag tint="blue">My tag</Tag>
    </MainNavigationItem>
  );
  expect(screen.getByText('My title')).toBeInTheDocument();
  expect(screen.getByText('My tag')).toBeInTheDocument();
});

test('MainNavigationItem doesnt support multiple tags', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();

  expect(() =>
    render(
      <MainNavigationItem icon={<CardIcon />}>
        My title
        <Tag tint="blue">My tag</Tag>
        <Tag tint="purple">My tag</Tag>
      </MainNavigationItem>
    )
  ).toThrowError('You can only provide one component of type Tag.');

  mockConsole.mockRestore();
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
