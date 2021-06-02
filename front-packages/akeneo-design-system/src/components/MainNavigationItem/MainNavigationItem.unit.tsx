import React from 'react';
import {MainNavigationItem} from './MainNavigationItem';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {ComponentIcon} from '../../icons';

test('MainNavigationItem displays title', () => {
  render(<MainNavigationItem icon={<ComponentIcon />} title="My title" />);
  expect(screen.getByText('My title')).toBeInTheDocument();
});

test('MainNavigationItem displays tags', () => {
  render(<MainNavigationItem icon={<ComponentIcon />} title="My title" tag="My tag" />);
  expect(screen.getByText('My title')).toBeInTheDocument();
  expect(screen.getByText('My tag')).toBeInTheDocument();
});

test('MainNavigationItem triggers onClick when MainNavigationItem is clicked', () => {
  const onClick = jest.fn();
  render(<MainNavigationItem icon={<ComponentIcon />} title="My title" onClick={onClick} />);

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).toBeCalled();
});

test('MainNavigationItem will not trigger onClick when MainNavigationItem is disabled', () => {
  const onClick = jest.fn();
  render(<MainNavigationItem icon={<ComponentIcon />} title="My title" onClick={onClick} disabled={true} />);

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).not.toBeCalled();
});

test('MainNavigationItem supports forwardRef', () => {
  const ref = {current: null};

  render(<MainNavigationItem icon={<ComponentIcon />} title="My title" ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('MainNavigationItem supports ...rest props', () => {
  render(<MainNavigationItem icon={<ComponentIcon />} title="My title" data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
