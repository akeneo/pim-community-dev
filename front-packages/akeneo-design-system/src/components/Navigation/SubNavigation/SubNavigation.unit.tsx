import userEvent from '@testing-library/user-event';
import React from 'react';
import {fireEvent, render, screen} from '../../../storybook/test-util';
import {Item} from './Item/Item';
import {Section} from './Section/Section';
import {SubNavigation} from './SubNavigation';

test('it renders its children', () => {
  const handleCollapse = jest.fn();
  render(<SubNavigation onCollapse={handleCollapse}>Content</SubNavigation>);
  expect(screen.getByText('Content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const handleCollapse = jest.fn();
  const ref = {current: null};
  render(<SubNavigation ref={ref} onCollapse={handleCollapse} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  const handleCollapse = jest.fn();
  render(<SubNavigation data-testid="my_value" onCollapse={handleCollapse} />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt render its children when collapsed', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigation isOpen={false} onCollapse={handleCollapse}>
      Content
    </SubNavigation>
  );
  expect(screen.queryByText('Content')).toBeNull();
});

test('it calls the onCollapse handler when hitting the close button', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigation isOpen={true} onCollapse={handleCollapse}>
      Content
    </SubNavigation>
  );
  userEvent.click(screen.getByTitle('Close'));
  expect(handleCollapse).toHaveBeenCalledWith(false);
});

test('it calls the onCollapse handler when hitting the open button', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigation isOpen={false} onCollapse={handleCollapse}>
      Content
    </SubNavigation>
  );
  userEvent.click(screen.getByTitle('Open'));
  expect(handleCollapse).toHaveBeenCalledWith(true);
});

test('it renders SubNavigation.Section(s) & SubNavigation.Item(s)', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigation onCollapse={handleCollapse}>
      <Item>Item 1</Item>
      <Section>
        <Item>Item 2</Item>
      </Section>
    </SubNavigation>
  );
  expect(screen.getByText('Item 1')).toBeInTheDocument();
  expect(screen.getByText('Item 2')).toBeInTheDocument();
});

test('it renders a dropdown navigation menu when collapsed with only the SubNavigation.Item(s) defined inside the SubNavigation.Section(s).', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigation isOpen={false} onCollapse={handleCollapse}>
      <Item>Ignored Item</Item>
      <Section>
        <Item>Item</Item>
      </Section>
    </SubNavigation>
  );
  fireEvent.click(screen.getByTitle('Navigation'));
  expect(screen.queryByText('Ignored Item')).toBeNull();
  expect(screen.getByText('Item')).toBeInTheDocument();
});
