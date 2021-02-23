import React from 'react';
import {List} from './List';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<List>List content</List>);

  expect(screen.getByText('List content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('List supports forwardRef', () => {
  const ref = {current: null};

  render(<List ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('List supports ...rest props', () => {
  render(<List data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
