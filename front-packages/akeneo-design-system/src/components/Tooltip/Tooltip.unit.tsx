import React from 'react';
import {fireEvent, render, screen} from '../../storybook/test-util';
import {Tooltip} from './Tooltip';

test('it renders its children properly', () => {
  render(<Tooltip content={'Tooltip content'} data-testid="my_value" />);
  fireEvent.mouseOver(screen.getByTestId('my_value'));
  expect(screen.getByText('Tooltip content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Tooltip supports forwardRef', () => {
  const ref = {current: null};

  render(<Tooltip content={'Tooltip content'} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tooltip supports ...rest props', () => {
  render(<Tooltip content={'Tooltip content'} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
