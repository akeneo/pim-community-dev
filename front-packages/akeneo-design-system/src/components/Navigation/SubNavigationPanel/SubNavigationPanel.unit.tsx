import React from 'react';
import {SubNavigationPanel} from './SubNavigationPanel';
import {render, screen} from '../../../storybook/test-util';
import userEvent from '@testing-library/user-event';

test('it renders its children properly', () => {
  const handleCollapse = jest.fn();
  render(<SubNavigationPanel onCollapse={handleCollapse}>SubNavigationPanel content</SubNavigationPanel>);
  expect(screen.getByText('SubNavigationPanel content')).toBeInTheDocument();
});

test('it supports forwardRef', () => {
  const ref = {current: null};
  const handleCollapse = jest.fn();

  render(<SubNavigationPanel ref={ref} onCollapse={handleCollapse} />);
  expect(ref.current).not.toBe(null);
});

test('it supports ...rest props', () => {
  const handleCollapse = jest.fn();
  render(<SubNavigationPanel onCollapse={handleCollapse} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});

test('it doesnt render its children when collapsed', () => {
  const handleCollapse = jest.fn();
  render(
    <SubNavigationPanel isOpen={false} onCollapse={handleCollapse}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );
  expect(screen.queryByText('SubNavigationPanel content')).toBeNull();
});

test('it calls the onCollapse handler when hitting the close button', () => {
  const handleCollapse = jest.fn();

  render(
    <SubNavigationPanel isOpen={true} onCollapse={handleCollapse}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );

  userEvent.click(screen.getByTitle('Close'));

  expect(handleCollapse).toHaveBeenCalledWith(false);
});

test('it calls the onCollapse handler when hitting the open button', () => {
  const handleCollapse = jest.fn();

  render(
    <SubNavigationPanel isOpen={false} onCollapse={handleCollapse}>
      SubNavigationPanel content
    </SubNavigationPanel>
  );

  userEvent.click(screen.getByTitle('Open'));

  expect(handleCollapse).toHaveBeenCalledWith(true);
});
