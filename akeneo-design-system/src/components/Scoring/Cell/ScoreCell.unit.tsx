import React from 'react';
import {ScoreCell} from './ScoreCell';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(<ScoreCell score="a" />);

  expect(screen.getByText('a')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('ScoreCell supports forwardRef', () => {
  const ref = {current: null};

  render(<ScoreCell ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('ScoreCell supports ...rest props', () => {
  render(<ScoreCell data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
