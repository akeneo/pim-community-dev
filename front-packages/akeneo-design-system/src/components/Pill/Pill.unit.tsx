import React from 'react';
import {Pill} from './Pill';
import {render, screen} from '../../storybook/test-util';

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Pill supports forwardRef', () => {
  const ref = {current: null};

  render(<Pill ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Pill supports ...rest props', () => {
  render(<Pill data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
