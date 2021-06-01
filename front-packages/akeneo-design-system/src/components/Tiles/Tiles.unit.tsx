import React from 'react';
import {Tiles} from './Tiles';
import {render, screen} from '../../storybook/test-util';

test('it renders its children properly', () => {
  render(<Tiles>Tiles content</Tiles>);

  expect(screen.getByText('Tiles content')).toBeInTheDocument();
});

// Those tests should pass directly if you follow the contributing guide.
// If you add required props to your Component, these tests will fail
// and you will need to add these required props here as well
test('Tiles supports forwardRef', () => {
  const ref = {current: null};

  render(<Tiles ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Tiles supports ...rest props', () => {
  render(<Tiles data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
