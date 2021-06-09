import React from 'react';
import {SubNavigationPanel} from './SubNavigationPanel';
import {render, screen} from '../../../storybook/test-util';

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

test('it can be closed', () => {
  render(<SubNavigationPanel isOpen={false}>SubNavigationPanel content</SubNavigationPanel>);
  expect(screen.queryByText('SubNavigationPanel content')).toBeNull();
});
