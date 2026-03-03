import userEvent from '@testing-library/user-event';
import React from 'react';
import {render, screen} from '../../../storybook/test-util';
import {SubNavigationPanel} from './SubNavigationPanel';

test('it renders its children properly', () => {
  const open = jest.fn();
  const close = jest.fn();
  render(
    <SubNavigationPanel open={open} close={close}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );
  expect(screen.getByText('SubNavigationPanel content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  const open = jest.fn();
  const close = jest.fn();
  render(<SubNavigationPanel open={open} close={close} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  const open = jest.fn();
  const close = jest.fn();
  render(<SubNavigationPanel open={open} close={close} data-testid="my_value" />);
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
    <SubNavigationPanel open={open} close={close} isOpen={isOpen} closeTitle="Close" openTitle="Open">
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
    <SubNavigationPanel open={open} close={close} isOpen={isOpen} closeTitle="Close" openTitle="Open">
      SubNavigationPanel content
    </SubNavigationPanel>
  );
  userEvent.click(screen.getByTitle('Open'));
  expect(screen.getByTitle('Open')).toBeInTheDocument();
  expect(screen.queryByTitle('Close')).toBeFalsy();
});

test('it shows collapsed content', () => {
  const open = jest.fn();
  const close = jest.fn();
  const {getByText, queryByText} = render(
    <SubNavigationPanel open={open} close={close} isOpen={false}>
      <SubNavigationPanel.Collapsed>Collapsed content</SubNavigationPanel.Collapsed>
      SubNavigationPanel content
    </SubNavigationPanel>
  );

  expect(queryByText('SubNavigationPanel content')).not.toBeInTheDocument();
  expect(getByText('Collapsed content')).toBeInTheDocument();
});

test('it hides collapsed content', () => {
  const open = jest.fn();
  const close = jest.fn();
  const {getByText, queryByText} = render(
    <SubNavigationPanel open={open} close={close} isOpen={true}>
      <SubNavigationPanel.Collapsed>Collapsed content</SubNavigationPanel.Collapsed>
      SubNavigationPanel content
    </SubNavigationPanel>
  );

  expect(getByText('SubNavigationPanel content')).toBeInTheDocument();
  expect(queryByText('Collapsed content')).not.toBeInTheDocument();
});
