import React from 'react';
import {SubNavigationPanel} from './SubNavigationPanel';
import {render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders its children', () => {
  const handleCollapse = jest.fn();
  render(<SubNavigationPanel onCollapse={handleCollapse}>Content</SubNavigationPanel>);
  expect(screen.getByText('Content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const handleCollapse = jest.fn();
  const ref = {current: null};
  render(<SubNavigationPanel ref={ref} onCollapse={handleCollapse} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  const handleCollapse = jest.fn();
  render(<SubNavigationPanel data-testid="my_value" onCollapse={handleCollapse} />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt render its children when collapsed', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigationPanel isOpen={false} onCollapse={handleCollapse}>
      Content
    </SubNavigationPanel>
  );
  expect(screen.queryByText('Content')).toBeNull();
});

test('it calls the onCollapse handler when hitting the close button', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigationPanel isOpen={true} onCollapse={handleCollapse}>
      Content
    </SubNavigationPanel>
  );
  userEvent.click(screen.getByTitle('Close'));
  expect(handleCollapse).toHaveBeenCalledWith(false);
});

test('it calls the onCollapse handler when hitting the open button', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigationPanel isOpen={false} onCollapse={handleCollapse}>
      Content
    </SubNavigationPanel>
  );
  userEvent.click(screen.getByTitle('Open'));
  expect(handleCollapse).toHaveBeenCalledWith(true);
});

test('it doesnt render "Collapse" children', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigationPanel onCollapse={handleCollapse}>
      <SubNavigationPanel.Collapse>Content</SubNavigationPanel.Collapse>
    </SubNavigationPanel>
  );
  expect(screen.queryByText('Content')).toBeNull();
});

test('it renders "Collapse" children when collapsed', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigationPanel onCollapse={handleCollapse} isOpen={false}>
      <SubNavigationPanel.Collapse>Content</SubNavigationPanel.Collapse>
    </SubNavigationPanel>
  );
  expect(screen.getByText('Content')).toBeInTheDocument();
});
