import userEvent from '@testing-library/user-event';
import React from 'react';
import {render, screen} from '../../../storybook/test-util';
import {SubNavigationPanel} from './SubNavigationPanel';

test('it renders its children properly', () => {
  render(
    <SubNavigationPanel open={() => {}} close={() => {}}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );
  expect(screen.getByText('SubNavigationPanel content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  render(<SubNavigationPanel open={() => {}} close={() => {}} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  render(<SubNavigationPanel open={() => {}} close={() => {}} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it closes when hitting the toggle button while opened', () => {
  let isOpen = true;
  const open = () => {
    isOpen = true;
  };
  const close = () => {
    isOpen = false;
  };
  render(
    <SubNavigationPanel open={open} close={close} isOpen={isOpen}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );
  userEvent.click(screen.getByTitle('Close'));
  expect(screen.getByTitle('Close')).toBeInTheDocument();
  expect(screen.queryByTitle('Open')).toBeFalsy();
});

test('it opens when hitting the toggle button while closed', () => {
  let isOpen = false;
  const open = () => {
    isOpen = true;
  };
  const close = () => {
    isOpen = false;
  };
  render(
    <SubNavigationPanel open={open} close={close} isOpen={isOpen}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );
  userEvent.click(screen.getByTitle('Open'));
  expect(screen.getByTitle('Open')).toBeInTheDocument();
  expect(screen.queryByTitle('Close')).toBeFalsy();
});
