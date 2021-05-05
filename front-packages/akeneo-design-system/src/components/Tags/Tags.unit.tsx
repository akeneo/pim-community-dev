import React from 'react';
import {Tags} from './Tags';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Tags>Tags content</Tags>);

  expect(screen.getByText('Tags content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Tags supports forwardRef', () => {
  const ref = {current: null};

  render(<Tags ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tags supports ...rest props', () => {
  render(<Tags data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
