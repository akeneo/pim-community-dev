import React from 'react';
import {IconButton} from './IconButton';
import {render, screen} from '../../storybook/test-util';
import {ActivityIcon} from '../../icons';

test('it renders its children properly', () => {
  render(<IconButton icon={<ActivityIcon />} />);

  expect(screen.getByRole('button')).toBeInTheDocument();
});

test('IconButton supports forwardRef', () => {
  const ref = {current: null};

  render(<IconButton icon={<ActivityIcon />} ref={ref} />);
  expect(ref.current).not.toBe(null);
});

test('IconButton supports ...rest props', () => {
  render(<IconButton icon={<ActivityIcon />} data-testid="my_value" />);
  expect(screen.getByTestId('my_value')).toBeInTheDocument();
});
