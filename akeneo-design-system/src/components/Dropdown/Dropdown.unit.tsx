import React from 'react';
import {Dropdown} from './Dropdown';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Dropdown>Dropdown content</Dropdown>);

  expect(screen.getByText('Dropdown content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Dropdown supports forwardRef', () => {
  const ref = {current: null};

  render(<Dropdown ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Dropdown supports ...rest props', () => {
  render(<Dropdown data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
