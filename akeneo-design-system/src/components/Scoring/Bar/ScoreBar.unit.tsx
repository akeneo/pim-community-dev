import React from 'react';
import {ScoreBar} from './ScoreBar';
import {render, screen} from '../../../storybook/test-util';

test('it renders its children properly', () => {
  render(<ScoreBar />);

  expect(screen.getByText('a')).toBeInTheDocument();
  expect(screen.getByText('b')).toBeInTheDocument();
  expect(screen.getByText('c')).toBeInTheDocument();
  expect(screen.getByText('d')).toBeInTheDocument();
  expect(screen.getByText('e')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('ScoreBar supports forwardRef', () => {
  const ref = {current: null};

  render(<ScoreBar ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('ScoreBar supports ...rest props', () => {
  render(<ScoreBar data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
