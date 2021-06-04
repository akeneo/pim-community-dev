import React from 'react';
import {SubNavigationItem} from './SubNavigationItem';
import {fireEvent, render, screen} from '../../storybook/test-util';

test('SubNavigationItem supports forwardRef', () => {
  const ref = {current: null};

  render(<SubNavigationItem title="My title" ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('SubNavigationItem supports ...rest props', () => {
  render(<SubNavigationItem title="My title" data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('SubNavigationItem displays title', () => {
  render(<SubNavigationItem title="My title" />);
  expect(screen.getByText('My title')).toBeInTheDocument();
});

test('SubNavigationItem displays tags', () => {
  render(<SubNavigationItem title="My title" tag="My tag" />);
  expect(screen.getByText('My title')).toBeInTheDocument();
  expect(screen.getByText('My tag')).toBeInTheDocument();
});

test('SubNavigationItem triggers onClick when SubNavigationItem is clicked', () => {
  const onClick = jest.fn();
  render(<SubNavigationItem title="My title" onClick={onClick} />);

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).toBeCalled();
});

test('SubNavigationItem will not trigger onClick when SubNavigationItem is disabled', () => {
  const onClick = jest.fn();
  render(<SubNavigationItem title="My title" onClick={onClick} disabled={true} />);

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).not.toBeCalled();
});

test('SubNavigationItem will not trigger onClick when onClick is undefined', () => {
  const onClick = jest.fn();
  render(<SubNavigationItem title="My title" onClick={undefined} />);

  fireEvent.click(screen.getByText('My title'));

  expect(onClick).not.toBeCalled();
});

test('SubNavigationItem displays an anchor when providing a `href`', () => {
  render(<SubNavigationItem title="My title" href="https://akeneo.com/" />);
  expect(screen.getByText('My title').closest('a')).toHaveAttribute('href', 'https://akeneo.com/');
});

test('SubNavigationItem do not display an anchor when providing a `href` on a disabled component', () => {
  render(<SubNavigationItem title="My title" disabled={true} href="https://akeneo.com/" />);
  expect(screen.getByText('My title').closest('a')).not.toHaveAttribute('href');
});
