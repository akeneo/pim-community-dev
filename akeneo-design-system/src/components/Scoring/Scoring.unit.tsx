import React from 'react';
import {render, screen} from '../../storybook/test-util';
import {Scoring} from './Scoring';

test('it renders its children properly', () => {
  render(<Scoring bar />);

  expect(screen.getByText('a')).toBeInTheDocument();
  expect(screen.getByText('b')).toBeInTheDocument();
  expect(screen.getByText('c')).toBeInTheDocument();
  expect(screen.getByText('d')).toBeInTheDocument();
  expect(screen.getByText('e')).toBeInTheDocument();
});

test('it renders its children properly', () => {
  render(<Scoring score="a" />);

  expect(screen.getByText('a')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Scoring supports forwardRef', () => {
  const ref = {current: null};

  render(<Scoring ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Scoring supports ...rest props', () => {
  render(<Scoring data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
