import React from 'react';
import {fireEvent, render, screen} from '../../../../storybook/test-util';
import {Tag} from '../../../Tags/Tags';
import {Item} from './Item';

test('it is an anchor', () => {
  render(<Item href="https://my-site.test/">Content</Item>);
  expect(screen.getByText('Content').closest('a')).toHaveAttribute('href', 'https://my-site.test/');
});

test('it renders its children', () => {
  render(<Item>Content</Item>);
  expect(screen.getByText('Content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  render(<Item ref={ref}>Content</Item>);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  render(<Item data-testid="my_value">Content</Item>);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt set the attribute href when it is disabled', () => {
  render(
    <Item href="https://my-site.test" disabled>
      Content
    </Item>
  );
  expect(screen.getByText('Content').closest('a')).not.toHaveAttribute('href', 'https://my-site.test/');
});

test('it triggers onClick when it is clicked', () => {
  const onClick = jest.fn();
  render(<Item onClick={onClick}>Content</Item>);
  fireEvent.click(screen.getByText('Content'));
  expect(onClick).toBeCalled();
});

test('it doesnt trigger onClick when it is disabled', () => {
  const onClick = jest.fn();
  render(
    <Item onClick={onClick} disabled>
      Content
    </Item>
  );
  fireEvent.click(screen.getByText('Content'));
  expect(onClick).not.toBeCalled();
});

test('it supports a Tag', () => {
  render(
    <Item>
      Content
      <Tag tint="blue">New</Tag>
    </Item>
  );
  expect(screen.getByText('New')).toBeInTheDocument();
});

test('it throws if there is multiple Tags', () => {
  const mockConsole = jest.spyOn(console, 'error').mockImplementation();
  expect(() => {
    render(
      <Item>
        Content
        <Tag tint="blue">New</Tag>
        <Tag tint="purple">Old</Tag>
      </Item>
    );
  }).toThrowError('You can only provide one component of type Tag.');
  mockConsole.mockRestore();
});
