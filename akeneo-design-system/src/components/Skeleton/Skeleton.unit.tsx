import React from 'react';
import {Skeleton} from './Skeleton';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Skeleton>Skeleton content</Skeleton>);

  expect(screen.getByText('Skeleton content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Skeleton supports forwardRef', () => {
  const ref = {current: null};

  render(<Skeleton ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Skeleton supports ...rest props', () => {
  render(<Skeleton data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
