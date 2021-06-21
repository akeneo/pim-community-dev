import userEvent from '@testing-library/user-event';
import React from 'react';
import {render, screen} from '../../../storybook/test-util';
import {SubNavigationPanel} from './SubNavigationPanel';

test('it renders its children properly', () => {
  render(<SubNavigationPanel>SubNavigationPanel content</SubNavigationPanel>);
  expect(screen.getByText('SubNavigationPanel content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  render(<SubNavigationPanel ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  render(<SubNavigationPanel data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt render its children when collapsed', () => {
  render(<SubNavigationPanel isOpen={false}>SubNavigationPanel content</SubNavigationPanel>);
  expect(screen.queryByText('SubNavigationPanel content')).toBeNull();
});

test('it closes when hitting the toggle button while opened', () => {
  render(<SubNavigationPanel isOpen={true}>SubNavigationPanel content</SubNavigationPanel>);
  userEvent.click(screen.getByTitle('Close'));
  expect(screen.queryByText('SubNavigationPanel content')).toBeNull();
});

test('it opens when hitting the toggle button while closed', () => {
  render(<SubNavigationPanel isOpen={false}>SubNavigationPanel content</SubNavigationPanel>);
  userEvent.click(screen.getByTitle('Open'));
  expect(screen.getByText('SubNavigationPanel content')).toBeInTheDocument();
});
