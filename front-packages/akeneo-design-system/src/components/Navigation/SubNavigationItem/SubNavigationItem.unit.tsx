import React from 'react';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {Tag} from '../../Tags/Tags';
import {SubNavigationItem} from './SubNavigationItem';

test('it is an anchor', () => {
  render(<SubNavigationItem href="https://my-site.test/">Content</SubNavigationItem>);
  expect(screen.getByText('Content').closest('a')).toHaveAttribute('href', 'https://my-site.test/');
});

test('it renders its children', () => {
  render(<SubNavigationItem>Content</SubNavigationItem>);
  expect(screen.getByText('Content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  render(<SubNavigationItem ref={ref}>Content</SubNavigationItem>);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  render(<SubNavigationItem data-testid="my_value">Content</SubNavigationItem>);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt set the attribute href when it is disabled', () => {
  render(
    <SubNavigationItem href="https://my-site.test" disabled>
      Content
    </SubNavigationItem>
  );
  expect(screen.getByText('Content').closest('a')).not.toHaveAttribute('href', 'https://my-site.test/');
});

test('it triggers onClick when it is clicked', () => {
  const onClick = jest.fn();
  render(<SubNavigationItem onClick={onClick}>Content</SubNavigationItem>);
  fireEvent.click(screen.getByText('Content'));
  expect(onClick).toBeCalled();
});

test('it doesnt trigger onClick when it is disabled', () => {
  const onClick = jest.fn();
  render(
    <SubNavigationItem onClick={onClick} disabled>
      Content
    </SubNavigationItem>
  );
  fireEvent.click(screen.getByText('Content'));
  expect(onClick).not.toBeCalled();
});

test('it supports a Tag', () => {
  render(
    <SubNavigationItem>
      Content
      <Tag tint="blue">New</Tag>
    </SubNavigationItem>
  );
  expect(screen.getByText('New')).toBeInTheDocument();
});

test('it throws if there is multiple Tags', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  expect(() => {
    render(
      <SubNavigationItem>
        Content
        <Tag tint="blue">New</Tag>
        <Tag tint="purple">Old</Tag>
      </SubNavigationItem>
    );
  }).toThrowError('You can only provide one component of type Tag.');
  mockConsole.mockRestore();
});
