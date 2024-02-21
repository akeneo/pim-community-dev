import React from 'react';
import {Pill} from './Pill';
import {render, screen} from '../../storybook/test-util';

test('Pill supports forwardRef', () => {
  const ref = {current: null};

  render(<Pill ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('Pill supports ...rest props', () => {
  render(<Pill data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
